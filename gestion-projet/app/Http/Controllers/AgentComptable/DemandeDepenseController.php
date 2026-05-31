<?php

namespace App\Http\Controllers\AgentComptable;

use App\Enums\ModePaiement;
use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\DemandeDepense;
use App\Services\DemandeDepenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DemandeDepenseController extends Controller
{
    public function __construct(private readonly DemandeDepenseService $service) {}

    public function index(Request $request): Response
    {
        $enAttente = DemandeDepense::with([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle',
            'porteur:id_utilisateur,utilisateur_nom',
        ])
            ->enAttenteAgentComptable()
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $historique = DemandeDepense::with([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle',
            'porteur:id_utilisateur,utilisateur_nom',
        ])
            ->whereNotIn('demande_statut', [StatutDemande::Soumise->value, StatutDemande::ValideeDaf->value])
            ->when($request->filled('statut'), fn ($q) => $q->where('demande_statut', $request->statut))
            ->latest()
            ->paginate(20)
            ->through(fn (DemandeDepense $d) => $this->formatDemande($d));

        return Inertia::render('ac/Demandes/Index', [
            'en_attente' => $enAttente,
            'historique' => $historique,
            'filters' => $request->only(['statut']),
            'modes_paiement' => collect(ModePaiement::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
    }

    public function show(DemandeDepense $demande): Response
    {
        $demande->load([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle,rubrique_montant',
            'porteur:id_utilisateur,utilisateur_nom,utilisateur_email',
            'paiement.enregistreur:id_utilisateur,utilisateur_nom',
            'validateurDaf:id_utilisateur,utilisateur_nom',
            'validateurAgentComptable:id_utilisateur,utilisateur_nom',
        ]);

        return Inertia::render('ac/Demandes/Show', [
            'demande' => $this->formatDemandeDetail($demande),
            'modes_paiement' => collect(ModePaiement::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
    }

    public function valider(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $this->service->validerAgentComptable($demande, $request->user());

        return back()->with('success', 'Demande validée avec succès.');
    }

    public function rejeter(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $request->validate([
            'motif' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->rejeterAgentComptable($demande, $request->user(), $request->string('motif'));

        return back()->with('success', 'Demande rejetée.');
    }

    public function enregistrerPaiement(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $validated = $request->validate([
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
            'mode_paiement' => ['required', Rule::enum(ModePaiement::class)],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->enregistrerPaiement($demande, $request->user(), $validated);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    public function validerRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $this->service->validerRapportAgentComptable($demande, $request->user());

        return back()->with('success', 'Rapport validé.');
    }

    public function rejeterRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $validated = $request->validate([
            'motif' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->rejeterRapport($demande, $request->user(), $validated['motif'] ?? null);

        return back()->with('success', 'Rapport rejeté. Le porteur doit soumettre un nouveau rapport.');
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
            'porteur' => ['utilisateur_nom' => $d->porteur->utilisateur_nom],
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
            'rapport_motif_rejet' => $d->demande_rapport_motif_rejet,
            'rapport_validee_daf' => $d->demande_rapport_valide_daf,
            'rapport_validee_ac' => $d->demande_rapport_valide_ac,
            'validee_daf_at' => $d->demande_date_validation_daf?->toDateTimeString(),
            'validee_ac_at' => $d->demande_date_validation_ac?->toDateTimeString(),
            'validateur_daf' => $d->validateurDaf?->utilisateur_nom,
            'porteur_email' => $d->porteur->utilisateur_email,
            'rubrique_montant' => $d->rubrique->rubrique_montant,
            'paiement' => $d->paiement ? [
                'montant' => $d->paiement->paiement_montant,
                'date_paiement' => $d->paiement->paiement_date?->toDateString(),
                'mode_paiement' => $d->paiement->paiement_mode?->value ?? '',
                'mode_paiement_label' => $d->paiement->paiement_mode?->label() ?? '',
                'reference' => $d->paiement->paiement_reference,
                'enregistre_par' => $d->paiement->enregistreur->utilisateur_nom,
            ] : null,
        ];
    }
}
