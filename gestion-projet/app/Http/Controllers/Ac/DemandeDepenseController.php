<?php

namespace App\Http\Controllers\Ac;

use App\Enums\DemandeStatus;
use App\Enums\ModePaiement;
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
            'convention.projet:id,titre',
            'convention:id,titre,projet_id',
            'rubrique:id,libelle',
            'porteur:id,name',
        ])
            ->enAttenteAc()
            ->latest()
            ->get()
            ->map(fn (DemandeDepense $d) => $this->formatDemande($d));

        $historique = DemandeDepense::with([
            'convention.projet:id,titre',
            'convention:id,titre,projet_id',
            'rubrique:id,libelle',
            'porteur:id,name',
        ])
            ->whereNotIn('status', [DemandeStatus::Soumise->value, DemandeStatus::ValidéeDaf->value])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->through(fn (DemandeDepense $d) => $this->formatDemande($d));

        return Inertia::render('ac/Demandes/Index', [
            'en_attente' => $enAttente,
            'historique' => $historique,
            'filters' => $request->only(['status']),
            'modes_paiement' => collect(ModePaiement::cases())->map(fn ($m) => [
                'value' => $m->value,
                'label' => $m->label(),
            ]),
        ]);
    }

    public function show(DemandeDepense $demande): Response
    {
        $demande->load([
            'convention.projet:id,titre',
            'convention:id,titre,projet_id',
            'rubrique:id,libelle,montant_prevu',
            'porteur:id,name,email',
            'paiement.enregistrePar:id,name',
            'validateurDaf:id,name',
            'validateurAc:id,name',
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
        $this->service->validerAc($demande, $request->user());

        return back()->with('success', 'Demande validée avec succès.');
    }

    public function rejeter(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $request->validate([
            'motif' => ['required', 'string', 'max:1000'],
        ]);

        $this->service->rejeterAc($demande, $request->user(), $request->string('motif'));

        return back()->with('success', 'Demande rejetée.');
    }

    public function enregistrerPaiement(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
            'mode_paiement' => ['required', Rule::enum(ModePaiement::class)],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->service->enregistrerPaiement($demande, $request->user(), $validated);

        return back()->with('success', 'Paiement enregistré avec succès.');
    }

    public function validerRapport(Request $request, DemandeDepense $demande): RedirectResponse
    {
        $this->service->validerRapportAc($demande, $request->user());

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
            'objet' => $d->objet,
            'montant' => $d->montant,
            'status' => $d->status->value,
            'status_label' => $d->status->label(),
            'badge_class' => $d->status->badgeClass(),
            'created_at' => $d->created_at->toDateString(),
            'porteur' => ['name' => $d->porteur->name],
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
            'has_justificatif' => $d->has_justificatif,
            'has_rapport' => $d->has_rapport,
            'rapport_validee_daf' => $d->rapport_validee_daf,
            'rapport_validee_ac' => $d->rapport_validee_ac,
            'validee_daf_at' => $d->validee_daf_at?->toDateTimeString(),
            'validee_ac_at' => $d->validee_ac_at?->toDateTimeString(),
            'validateur_daf' => $d->validateurDaf?->name,
            'porteur_email' => $d->porteur->email,
            'rubrique_montant_prevu' => $d->rubrique->montant_prevu,
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
