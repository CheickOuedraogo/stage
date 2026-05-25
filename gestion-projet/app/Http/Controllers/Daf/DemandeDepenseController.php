<?php

namespace App\Http\Controllers\Daf;

use App\Enums\StatutDemande;
use App\Http\Controllers\Controller;
use App\Models\DemandeDepense;
use App\Services\DemandeDepenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->enAttenteDaf()
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $historique = DemandeDepense::with([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle',
            'porteur:id_utilisateur,utilisateur_nom',
        ])
            ->whereNotIn('demande_statut', [StatutDemande::Soumise->value])
            ->when($request->filled('statut'), fn ($q) => $q->where('demande_statut', $request->statut))
            ->latest()
            ->paginate(20)
            ->through(fn (DemandeDepense $d) => $this->formatDemande($d));

        return Inertia::render('daf/Demandes/Index', [
            'en_attente' => $enAttente,
            'historique' => $historique,
            'filters' => $request->only(['statut']),
            'statuses' => collect(StatutDemande::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(),
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
            'paiement.enregistrePar:id_utilisateur,utilisateur_nom',
            'validateurDaf:id_utilisateur,utilisateur_nom',
            'validateurAgentComptable:id_utilisateur,utilisateur_nom',
        ]);

        return Inertia::render('daf/Demandes/Show', [
            'demande' => $this->formatDemandeDetail($demande),
        ]);
    }

    public function valider(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $this->service->validerDaf($demande, $request->user());

        return back()->with('success', 'Demande validée avec succès.');
    }

    public function rejeter(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $request->validate([
            'motif' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->rejeterDaf($demande, $request->user(), $request->motif);

        return back()->with('success', 'Demande rejetée.');
    }

    public function validerRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $this->service->validerRapportDaf($demande, $request->user());

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
            'cree_le' => $d->created_at?->toDateString(),
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
            'validateur_ac' => $d->validateurAgentComptable?->utilisateur_nom,
            'porteur_email' => $d->porteur->utilisateur_email,
            'rubrique_montant' => $d->rubrique->rubrique_montant,
            'paiement' => $d->paiement ? [
                'montant' => $d->paiement->paiement_montant,
                'date_paiement' => $d->paiement->paiement_date?->toDateString(),
                'mode_paiement' => $d->paiement->paiement_mode->value,
                'mode_paiement_label' => $d->paiement->paiement_mode->label(),
                'reference' => $d->paiement->paiement_reference,
                'enregistre_par' => $d->paiement->enregistrePar->utilisateur_nom,
            ] : null,
        ];
    }
}
