<?php

namespace App\Http\Controllers\Porteur;

use App\Enums\StatutConvention;
use App\Enums\StatutDemande;
use App\Enums\StatutProjet;
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
            ->where('id_porteur', $porteur->id_utilisateur)
            ->when($request->filled('statut'), fn ($q) => $q->where('demande_statut', $request->statut))
            ->when($request->filled('convention_id'), fn ($q) => $q->where('id_convention', $request->convention_id))
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $conventions = Convention::whereHas('projet', fn ($q) => $q->where('id_porteur', $porteur->id_utilisateur))
            ->select('id_convention', 'convention_titre', 'id_projet')
            ->with('projet:id_projet,projet_titre')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id_convention,
                'titre' => $c->convention_titre,
                'projet_titre' => $c->projet->projet_titre,
            ]);

        return Inertia::render('porteur/Demandes/Index', [
            'demandes' => $demandes,
            'conventions' => $conventions,
            'filters' => $request->only(['statut', 'convention_id']),
            'statuses' => collect(StatutDemande::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    public function create(Request $request, Projet $projet, Convention $convention): Response
    {
        $porteur = $request->user();
        abort_unless($projet->id_porteur === $porteur->id_utilisateur, 403);
        abort_unless($convention->id_projet === $projet->id_projet, 404);
        abort_unless($projet->projet_statut === StatutProjet::EnCours, 403);
        abort_unless($convention->convention_statut === StatutConvention::Active, 403);

        $this->service->assertConventionARubriques($convention);
        $this->service->assertPasDeDemandeActive($convention);

        $rubriques = $convention->rubriques()
            ->get()
            ->map(fn (Rubrique $r) => [
                'id' => $r->id_rubrique,
                'libelle' => $r->rubrique_libelle,
                'montant_prevu' => $r->rubrique_montant,
                'description' => $r->rubrique_description,
            ]);

        return Inertia::render('porteur/Demandes/Create', [
            'projet' => ['id' => $projet->id_projet, 'titre' => $projet->projet_titre],
            'convention' => [
                'id' => $convention->id_convention,
                'titre' => $convention->convention_titre,
                'montant_fcfa' => $convention->montant_fcfa,
            ],
            'rubriques' => $rubriques,
        ]);
    }

    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        $porteur = $request->user();
        abort_unless($projet->id_porteur === $porteur->id_utilisateur, 403);
        abort_unless($convention->id_projet === $projet->id_projet, 404);
        abort_unless($projet->projet_statut === StatutProjet::EnCours, 403);
        abort_unless($convention->convention_statut === StatutConvention::Active, 403);

        $validated = $request->validate([
            'rubrique_id' => ['required', 'integer', Rule::exists('rubriques', 'id_rubrique')->where('id_convention', $convention->id_convention)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'justificatif' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $this->service->assertConventionARubriques($convention);
        $this->service->assertPasDeDemandeActive($convention);

        $rubrique = Rubrique::findOrFail($validated['rubrique_id']);
        $this->service->assertBudgetSuffisant($rubrique, $validated['montant']);

        $justificatifPath = $request->file('justificatif')->store('justificatifs', 'private');

        DemandeDepense::create([
            'id_rubrique' => $validated['rubrique_id'],
            'id_convention' => $convention->id_convention,
            'id_porteur' => $porteur->id_utilisateur,
            'demande_montant' => $validated['montant'],
            'demande_objet' => $validated['objet'],
            'demande_description' => $validated['description'] ?? null,
            'demande_justificatif' => $justificatifPath,
            'demande_statut' => StatutDemande::Soumise,
        ]);

        return redirect()->route('porteur.demandes.index')
            ->with('success', 'Demande de dépense soumise avec succès.');
    }

    public function show(Request $request, DemandeDepense $demande): Response
    {
        abort_unless($demande->id_porteur === $request->user()->id_utilisateur, 403);

        $demande->load([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle,rubrique_montant',
            'paiement.enregistrePar:id_utilisateur,utilisateur_nom',
            'validateurDaf:id_utilisateur,utilisateur_nom',
            'validateurAgentComptable:id_utilisateur,utilisateur_nom',
        ]);

        return Inertia::render('porteur/Demandes/Show', [
            'demande' => $this->formatDemandeDetail($demande),
        ]);
    }

    public function uploadRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        abort_unless($demande->id_porteur === $request->user()->id_utilisateur, 403);

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
            $demande->id_porteur === $request->user()->id_utilisateur
            || in_array($request->user()->role_key->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->demande_justificatif && Storage::disk('private')->exists($demande->demande_justificatif), 404);

        return Storage::disk('private')->download($demande->demande_justificatif, 'justificatif.pdf');
    }

    public function downloadRapport(Request $request, DemandeDepense $demande)
    {
        abort_unless(
            $demande->id_porteur === $request->user()->id_utilisateur
            || in_array($request->user()->role_key->value, ['daf', 'ac']),
            403
        );
        abort_unless($demande->demande_rapport && Storage::disk('private')->exists($demande->demande_rapport), 404);

        return Storage::disk('private')->download($demande->demande_rapport, 'rapport_execution.pdf');
    }

    private function formatDemande(DemandeDepense $d): array
    {
        return [
            'id' => $d->id_demande,
            'objet' => $d->demande_objet,
            'montant' => $d->demande_montant,
            'statut' => $d->demande_statut->value,
            'libelle_statut' => $d->demande_statut->label(),
            'badge_class' => $d->demande_statut->badgeClass(),
            'cree_le' => $d->cree_le?->toDateString(),
            'convention' => [
                'id' => $d->convention->id_convention,
                'titre' => $d->convention->convention_titre,
            ],
            'projet' => [
                'id' => $d->convention->projet->id_projet,
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
            'possede_justificatif' => $d->possede_justificatif,
            'possede_rapport' => $d->possede_rapport,
            'rapport_validee_daf' => $d->demande_rapport_valide_daf,
            'rapport_validee_ac' => $d->demande_rapport_valide_ac,
            'validee_daf_at' => $d->demande_date_validation_daf?->toDateTimeString(),
            'validee_ac_at' => $d->demande_date_validation_ac?->toDateTimeString(),
            'validateur_daf' => $d->validateurDaf?->utilisateur_nom,
            'validateur_ac' => $d->validateurAgentComptable?->utilisateur_nom,
            'paiement' => $d->paiement ? [
                'montant' => $d->paiement->paiement_montant,
                'date_paiement' => $d->paiement->paiement_date?->toDateString(),
                'mode_paiement' => $d->paiement->paiement_mode?->value ?? '',
                'mode_paiement_label' => $d->paiement->paiement_mode?->label() ?? '',
                'reference' => $d->paiement->paiement_reference,
                'enregistre_par' => $d->paiement->enregistrePar->utilisateur_nom,
            ] : null,
        ];
    }
}
