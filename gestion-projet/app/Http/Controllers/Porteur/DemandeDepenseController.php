<?php

namespace App\Http\Controllers\Porteur;

use App\Enums\ConventionStatus;
use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
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
            'convention:id_convention,convention_titre,id_projet',
            'convention.projet:id_projet,projet_titre',
            'rubrique:id_rubrique,rubrique_libelle',
        ])
            ->where('id_porteur', $porteur->id)
            ->when($request->filled('status'), fn ($q) => $q->where('demande_statut', $request->status))
            ->when($request->filled('convention_id'), fn ($q) => $q->where('id_convention', $request->convention_id))
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $conventions = Convention::whereHas('projet', fn ($q) => $q->where('id_porteur', $porteur->id))
            ->select('id', 'convention_titre', 'id_projet')
            ->with('projet:id_projet,projet_titre')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'titre' => $c->convention_titre,
                'projet_titre' => $c->projet->projet_titre,
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
        abort_unless($projet->id_porteur === $porteur->id, 403);
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($projet->projet_statut === ProjectStatus::EnCours, 403);
        abort_unless($convention->convention_statut === ConventionStatus::Active, 403);

        $this->service->assertPasDeDemandeActive($convention);

        $rubriques = $convention->rubriques()
            ->get()
            ->map(fn (Rubrique $r) => [
                'id' => $r->id,
                'libelle' => $r->rubrique_libelle,
                'montant_prevu' => $r->rubrique_montant_prevu,
                'description' => $r->rubrique_description,
            ]);

        return Inertia::render('porteur/Demandes/Create', [
            'projet' => ['id' => $projet->id, 'titre' => $projet->projet_titre],
            'convention' => [
                'id' => $convention->id,
                'titre' => $convention->convention_titre,
                'montant_fcfa' => $convention->convention_montant_fcfa,
            ],
            'rubriques' => $rubriques,
        ]);
    }

    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        $porteur = $request->user();
        abort_unless($projet->id_porteur === $porteur->id, 403);
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($projet->projet_statut === ProjectStatus::EnCours, 403);
        abort_unless($convention->convention_statut === ConventionStatus::Active, 403);

        $validated = $request->validate([
            'rubrique_id' => ['required', 'integer', Rule::exists('rubriques', 'id_rubrique')->where('id_convention', $convention->id)],
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
            'id_rubrique' => $validated['rubrique_id'],
            'id_convention' => $convention->id,
            'id_porteur' => $porteur->id,
            'demande_montant' => $validated['montant'],
            'demande_objet' => $validated['objet'],
            'demande_description' => $validated['description'] ?? null,
            'demande_justificatif' => $justificatifPath,
            'demande_statut' => DemandeStatus::Soumise,
        ]);

        return redirect()->route('porteur.demandes.index')
            ->with('success', 'Demande de dépense soumise avec succès.');
    }

    public function show(Request $request, DemandeDepense $demande): Response
    {
        abort_unless($demande->id_porteur === $request->user()->id, 403);

        $demande->load([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle,rubrique_montant_prevu',
            'paiement.enregistrePar:id_utilisateur,name',
            'validateurDaf:id_utilisateur,name',
            'validateurAc:id_utilisateur,name',
        ]);

        return Inertia::render('porteur/Demandes/Show', [
            'demande' => $this->formatDemandeDetail($demande),
        ]);
    }

    public function uploadRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        abort_unless($demande->id_porteur === $request->user()->id, 403);

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
            $demande->id_porteur === $request->user()->id
            || in_array($request->user()->utilisateur_role->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->demande_justificatif && Storage::disk('private')->exists($demande->demande_justificatif), 404);

        return Storage::disk('private')->download($demande->demande_justificatif, 'justificatif.pdf');
    }

    public function downloadRapport(Request $request, DemandeDepense $demande)
    {
        abort_unless(
            $demande->id_porteur === $request->user()->id
            || in_array($request->user()->utilisateur_role->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->demande_rapport && Storage::disk('private')->exists($demande->demande_rapport), 404);

        return Storage::disk('private')->download($demande->demande_rapport, 'rapport_execution.pdf');
    }

    private function formatDemande(DemandeDepense $d): array
    {
        return [
            'id' => $d->id,
            'objet' => $d->demande_objet,
            'montant' => $d->demande_montant,
            'status' => $d->demande_statut->value,
            'status_label' => $d->demande_statut->label(),
            'badge_class' => $d->demande_statut->badgeClass(),
            'created_at' => $d->created_at->toDateString(),
            'convention' => [
                'id' => $d->convention->id,
                'titre' => $d->convention->convention_titre,
            ],
            'projet' => [
                'id' => $d->convention->projet->id,
                'titre' => $d->convention->projet->projet_titre,
            ],
            'rubrique' => ['libelle' => $d->rubrique->rubrique_libelle],
        ];
    }

    private function formatDemandeDetail(DemandeDepense $d): array
    {
        return [
            ...$this->formatDemande($d),
            'description' => $d->demande_description,
            'motif_rejet' => $d->demande_motif_rejet,
            'has_justificatif' => $d->has_justificatif,
            'has_rapport' => $d->has_rapport,
            'rapport_validee_daf' => $d->demande_rapport_valide_daf,
            'rapport_validee_ac' => $d->demande_rapport_valide_ac,
            'validee_daf_at' => $d->demande_date_validation_daf?->toDateTimeString(),
            'validee_ac_at' => $d->demande_date_validation_ac?->toDateTimeString(),
            'validateur_daf' => $d->validateurDaf?->name,
            'validateur_ac' => $d->validateurAc?->name,
            'paiement' => $d->paiement ? [
                'montant' => $d->paiement->paiement_montant,
                'date_paiement' => $d->paiement->paiement_date->toDateString(),
                'mode_paiement' => $d->paiement->paiement_mode->value,
                'mode_paiement_label' => $d->paiement->paiement_mode->label(),
                'reference' => $d->paiement->paiement_reference,
                'enregistre_par' => $d->paiement->enregistrePar->name,
            ] : null,
        ];
    }
}
