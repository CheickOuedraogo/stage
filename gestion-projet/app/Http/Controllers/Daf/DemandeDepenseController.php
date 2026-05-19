<?php

namespace App\Http\Controllers\Daf;

use App\Enums\DemandeStatus;
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
            'porteur:id_utilisateur,name',
        ])
            ->enAttenteDaf()
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $historique = DemandeDepense::with([
            'convention.projet:id_projet,projet_titre',
            'convention:id_convention,convention_titre,id_projet',
            'rubrique:id_rubrique,rubrique_libelle',
            'porteur:id_utilisateur,name',
        ])
            ->whereNotIn('demande_statut', [DemandeStatus::Soumise->value])
            ->when($request->filled('status'), fn ($q) => $q->where('demande_statut', $request->status))
            ->latest()
            ->paginate(20)
            ->through(fn (DemandeDepense $d) => $this->formatDemande($d));

        return Inertia::render('daf/Demandes/Index', [
            'en_attente' => $enAttente,
            'historique' => $historique,
            'filters' => $request->only(['status']),
            'statuses' => collect(DemandeStatus::cases())->map(fn ($s) => [
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
            'rubrique:id_rubrique,rubrique_libelle,rubrique_montant_prevu',
            'porteur:id_utilisateur,name,email',
            'paiement.enregistrePar:id_utilisateur,name',
            'validateurDaf:id_utilisateur,name',
            'validateurAc:id_utilisateur,name',
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
        $this->service->rejeterRapport($demande, $request->user());

        return back()->with('success', 'Rapport rejeté. Le porteur doit soumettre un nouveau rapport.');
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
            'porteur' => ['name' => $d->porteur->name],
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
            'porteur_email' => $d->porteur->email,
            'rubrique_montant_prevu' => $d->rubrique->rubrique_montant_prevu,
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
