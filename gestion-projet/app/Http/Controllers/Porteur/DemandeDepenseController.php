<?php

namespace App\Http\Controllers\Porteur;

use App\Enums\DemandeStatus;
use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Services\DemandeDepenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DemandeDepenseController extends Controller
{
    public function __construct(private readonly DemandeDepenseService $service) {}

    public function index(Request $request): Response
    {
        $porteur = $request->user();

        $demandes = DemandeDepense::with([
            'convention:id,titre,projet_id',
            'convention.projet:id,titre',
            'rubrique:id,libelle',
        ])
            ->where('porteur_id', $porteur->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('convention_id'), fn ($q) => $q->where('convention_id', $request->convention_id))
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $conventions = Convention::whereHas('projet', fn ($q) => $q->where('porteur_id', $porteur->id))
            ->select('id', 'titre', 'projet_id')
            ->with('projet:id,titre')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'titre' => $c->titre,
                'projet_titre' => $c->projet->titre,
            ]);

        return Inertia::render('porteur/Demandes/Index', [
            'demandes' => $demandes,
            'conventions' => $conventions,
            'filters' => $request->only(['status', 'convention_id']),
            'statuses' => collect(DemandeStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function create(Request $request, Projet $projet, Convention $convention): Response
    {
        $porteur = $request->user();
        abort_unless($projet->porteur_id === $porteur->id, 403);
        abort_unless($convention->projet_id === $projet->id, 404);

        $this->service->assertPasDeDemandeActive($convention);

        $rubriques = $convention->rubriques()
            ->get()
            ->map(fn (Rubrique $r) => [
                'id' => $r->id,
                'libelle' => $r->libelle,
                'montant_prevu' => $r->montant_prevu,
                'description' => $r->description,
            ]);

        return Inertia::render('porteur/Demandes/Create', [
            'projet' => ['id' => $projet->id, 'titre' => $projet->titre],
            'convention' => [
                'id' => $convention->id,
                'titre' => $convention->titre,
                'montant_fcfa' => $convention->montant_fcfa,
            ],
            'rubriques' => $rubriques,
        ]);
    }

    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        $porteur = $request->user();
        abort_unless($projet->porteur_id === $porteur->id, 403);
        abort_unless($convention->projet_id === $projet->id, 404);

        $validated = $request->validate([
            'rubrique_id' => ['required', 'integer', Rule::exists('rubriques', 'id')->where('convention_id', $convention->id)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'justificatif' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $this->service->assertPasDeDemandeActive($convention);

        $rubrique = Rubrique::findOrFail($validated['rubrique_id']);
        $this->service->assertBudgetSuffisant($rubrique, $validated['montant']);

        $justificatifPath = $request->file('justificatif')->store('justificatifs', 'private');

        DemandeDepense::create([
            'rubrique_id' => $validated['rubrique_id'],
            'convention_id' => $convention->id,
            'porteur_id' => $porteur->id,
            'montant' => $validated['montant'],
            'objet' => $validated['objet'],
            'description' => $validated['description'] ?? null,
            'justificatif_path' => $justificatifPath,
            'status' => DemandeStatus::Soumise,
        ]);

        return redirect()->route('porteur.demandes.index')
            ->with('success', 'Demande de dépense soumise avec succès.');
    }

    public function show(Request $request, DemandeDepense $demande): Response
    {
        abort_unless($demande->porteur_id === $request->user()->id, 403);

        $demande->load([
            'convention.projet:id,titre',
            'convention:id,titre,projet_id',
            'rubrique:id,libelle,montant_prevu',
            'paiement.enregistrePar:id,name',
            'validateurDaf:id,name',
            'validateurAc:id,name',
        ]);

        return Inertia::render('porteur/Demandes/Show', [
            'demande' => $this->formatDemandeDetail($demande),
        ]);
    }

    public function uploadRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        abort_unless($demande->porteur_id === $request->user()->id, 403);

        $request->validate([
            'rapport' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $rapportPath = $request->file('rapport')->store('rapports', 'private');

        $this->service->soumettreRapport($demande, $request->user(), $rapportPath);

        return back()->with('success', 'Rapport d\'exécution soumis avec succès.');
    }

    public function downloadJustificatif(Request $request, DemandeDepense $demande)
    {
        abort_unless(
            $demande->porteur_id === $request->user()->id
            || in_array($request->user()->role->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->justificatif_path && Storage::disk('private')->exists($demande->justificatif_path), 404);

        return Storage::disk('private')->download($demande->justificatif_path, 'justificatif.pdf');
    }

    public function downloadRapport(Request $request, DemandeDepense $demande)
    {
        abort_unless(
            $demande->porteur_id === $request->user()->id
            || in_array($request->user()->role->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->rapport_path && Storage::disk('private')->exists($demande->rapport_path), 404);

        return Storage::disk('private')->download($demande->rapport_path, 'rapport_execution.pdf');
    }

    private function formatDemande(DemandeDepense $d): array
    {
        return [
            'id' => $d->id,
            'objet' => $d->objet,
            'montant' => $d->montant,
            'status' => $d->status->value,
            'status_label' => $d->status->label(),
            'badge_class' => $d->status->badgeClass(),
            'created_at' => $d->created_at->toDateString(),
            'convention' => [
                'id' => $d->convention->id,
                'titre' => $d->convention->titre,
            ],
            'projet' => [
                'id' => $d->convention->projet->id,
                'titre' => $d->convention->projet->titre,
            ],
            'rubrique' => ['libelle' => $d->rubrique->libelle],
        ];
    }

    private function formatDemandeDetail(DemandeDepense $d): array
    {
        return [
            ...$this->formatDemande($d),
            'description' => $d->description,
            'motif_rejet' => $d->motif_rejet,
            'has_justificatif' => (bool) $d->justificatif_path && Storage::disk('private')->exists($d->justificatif_path),
            'has_rapport' => (bool) $d->rapport_path && Storage::disk('private')->exists($d->rapport_path),
            'rapport_validee_daf' => $d->rapport_validee_daf,
            'rapport_validee_ac' => $d->rapport_validee_ac,
            'validee_daf_at' => $d->validee_daf_at?->toDateTimeString(),
            'validee_ac_at' => $d->validee_ac_at?->toDateTimeString(),
            'validateur_daf' => $d->validateurDaf?->name,
            'validateur_ac' => $d->validateurAc?->name,
            'paiement' => $d->paiement ? [
                'montant' => $d->paiement->montant,
                'date_paiement' => $d->paiement->date_paiement->toDateString(),
                'mode_paiement' => $d->paiement->mode_paiement->value,
                'mode_paiement_label' => $d->paiement->mode_paiement->label(),
                'reference' => $d->paiement->reference,
                'enregistre_par' => $d->paiement->enregistrePar->name,
            ] : null,
        ];
    }
}
