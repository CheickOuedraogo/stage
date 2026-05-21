<?php

namespace App\Http\Controllers\Daf;

use App\Enums\StatutDemande;
use App\Enums\StatutFinalProjet;
use App\Enums\StatutProjet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Daf\CloturerProjetRequest;
use App\Models\Convention;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjetController extends Controller
{
    public function __construct(private readonly ProjetService $projetService) {}

    public function index(): Response
    {
        $projets = Projet::with(['porteur:id_utilisateur,utilisateur_nom', 'conventions:id_convention,id_projet,convention_montant,convention_taux_conversion'])
            ->withCount('conventions')
            ->latest()
            ->get()
            ->map(fn (Projet $p) => [
                'id' => $p->id_utilisateur,
                'titre' => $p->projet_titre,
                'porteur' => $p->porteur->utilisateur_nom,
                'statut' => $p->projet_statut->value,
                'libelle_statut' => $p->projet_statut->label(),
                'montant_estime' => $p->projet_montant_estime,
                'montant_conventions' => $p->conventions->sum('montant_fcfa'),
                'conventions_count' => $p->conventions_count,
                'date_debut' => $p->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $p->projet_date_fin_prevue?->toDateString(),
            ]);

        return Inertia::render('daf/Projets/Index', [
            'projets' => $projets,
            'stats' => [
                'total' => Projet::count(),
                'en_cours' => Projet::where('projet_statut', 'en_cours')->count(),
                'total_budget' => Projet::sum('projet_montant_estime'),
                'total_conventions' => Convention::sum(\DB::raw('convention_montant * convention_taux_conversion')),
            ],
        ]);
    }

    public function show(Projet $projet): Response
    {
        $projet->load([
            'porteur:id_utilisateur,utilisateur_nom,utilisateur_email,utilisateur_telephone',
            'conventions' => fn ($q) => $q->with(['bailleur:id_bailleur,bailleur_nom,bailleur_sigle', 'rubriques:id_rubrique,id_convention,rubrique_libelle,rubrique_montant_prevu', 'versements:id_versement,id_convention,versement_montant,versement_date_reception']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');
        $montantConventions = $projet->conventions->sum('montant_fcfa');

        $canCloturer = false;
        $clotureBlockers = null;

        if ($projet->projet_statut === StatutProjet::EnCours) {
            $clotureBlockers = $this->projetService->getBlockersCloture($projet);
            $canCloturer = $clotureBlockers === null;
        }

        return Inertia::render('daf/Projets/Show', [
            'projet' => [
                'id' => $projet->id_utilisateur,
                'titre' => $projet->projet_titre,
                'description' => $projet->projet_description,
                'objectifs' => $projet->projet_objectifs,
                'statut' => $projet->projet_statut->value,
                'libelle_statut' => $projet->projet_statut->label(),
                'statut_final' => $projet->statut_final?->value,
                'statut_final_label' => $projet->statut_final?->label(),
                'montant_estime' => $projet->projet_montant_estime,
                'montant_conventions' => $montantConventions,
                'total_versements' => $totalVersements,
                'date_debut' => $projet->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
                'porteur' => [
                    'nom' => $projet->porteur->utilisateur_nom,
                    'utilisateur_email' => $projet->porteur->utilisateur_email,
                    'utilisateur_telephone' => $projet->porteur->utilisateur_telephone,
                ],
                'conventions' => $projet->conventions->map(fn (Convention $c) => [
                    'id' => $c->id_utilisateur,
                    'titre' => $c->convention_titre,
                    'bailleur' => ['nom' => $c->bailleur->bailleur_nom, 'sigle' => $c->bailleur->bailleur_sigle],
                    'montant_fcfa' => $c->montant_fcfa,
                    'forme' => $c->convention_forme->value,
                    'forme_label' => $c->convention_forme->label(),
                    'statut' => $c->convention_statut->value,
                    'libelle_statut' => $c->convention_statut->label(),
                    'total_rubriques' => $c->rubriques->sum('rubrique_montant_prevu'),
                    'total_versements' => $c->versements->sum('versement_montant'),
                    'rubriques_count' => $c->rubriques->count(),
                    'versements_count' => $c->versements->count(),
                ]),
                'analyse_ecarts' => $this->buildAnalyseEcarts($projet),
                'can_cloturer' => $canCloturer,
                'cloture_blockers' => $clotureBlockers,
                'bilan_url' => $projet->projet_statut === StatutProjet::Termine
                    ? route('daf.projets.bilan', $projet)
                    : null,
            ],
        ]);
    }

    public function cloturer(CloturerProjetRequest $request, Projet $projet): RedirectResponse
    {
        $this->authorize('cloturer', $projet);

        $projet->loadMissing('conventions');
        $this->projetService->verifierConditionsCloture($projet);
        $this->projetService->cloturer(
            $projet,
            auth()->user(),
            $request->date('date_fin_reelle'),
            StatutFinalProjet::from($request->validated('statut_final')),
        );

        return back()->with('success', "Le projet « {$projet->projet_titre} » a été clôturé avec succès.");
    }

    public function bilan(Projet $projet): Response
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        return Inertia::render('daf/Projets/Bilan', [
            'bilan' => $bilan,
            'pdf_url' => route('daf.projets.bilan.pdf', $projet),
        ]);
    }

    public function exporterBilanPdf(Projet $projet): HttpResponse
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))->setPaper('a4');

        return $pdf->download("bilan-projet-{$projet->id_utilisateur}.pdf");
    }

    /** @return array<string, mixed> */
    private function buildAnalyseEcarts(Projet $projet): array
    {
        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');

        $totalDepenses = $projet->conventions->flatMap->rubriques->flatMap->demandesDepenses
            ->whereIn('demande_statut', [
                StatutDemande::Payee->value,
                StatutDemande::RapportSoumis->value,
                StatutDemande::Terminee->value,
            ])->sum('demande_montant');

        $budgetPrevu = $projet->conventions->sum('montant_fcfa');
        $ecartBudget = $budgetPrevu - $totalDepenses;

        $ecartTemps = null;
        $ecartTempsLabel = null;

        if ($projet->projet_date_fin_prevue) {
            $dateRef = $projet->projet_date_fin_reelle ?? now();
            $ecartTemps = $projet->projet_date_fin_prevue->diffInDays($dateRef, false);
            $ecartTempsLabel = $ecartTemps > 0
                ? "{$ecartTemps} jour(s) de retard"
                : ($ecartTemps < 0 ? abs($ecartTemps).' jour(s) d\'avance' : 'Dans les délais');
        }

        return [
            'budget_prevu' => $budgetPrevu,
            'total_versements' => $totalVersements,
            'total_depenses' => $totalDepenses,
            'ecart_budget' => $ecartBudget,
            'taux_execution' => $budgetPrevu > 0 ? round(($totalDepenses / $budgetPrevu) * 100, 1) : 0,
            'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
            'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
            'ecart_temps_jours' => $ecartTemps,
            'ecart_temps_label' => $ecartTempsLabel,
        ];
    }
}
