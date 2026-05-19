<?php

namespace App\Http\Controllers\Daf;

use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjetStatutFinal;
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
        $projets = Projet::with(['porteur:id_utilisateur,name', 'conventions:id_convention,id_projet,convention_montant_fcfa'])
            ->withCount('conventions')
            ->latest()
            ->get()
            ->map(fn (Projet $p) => [
                'id' => $p->id,
                'titre' => $p->projet_titre,
                'porteur' => $p->porteur->name,
                'status' => $p->projet_statut->value,
                'status_label' => $p->projet_statut->label(),
                'montant_estime' => $p->projet_montant_estime,
                'montant_conventions' => $p->conventions->sum('convention_montant_fcfa'),
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
                'total_conventions' => Convention::sum('convention_montant_fcfa'),
            ],
        ]);
    }

    public function show(Projet $projet): Response
    {
        $projet->load([
            'porteur:id_utilisateur,name,email,telephone',
            'conventions' => fn ($q) => $q->with(['bailleur:id_bailleur,bailleur_nom,bailleur_sigle', 'rubriques:id_rubrique,id_convention,rubrique_libelle,rubrique_montant_prevu', 'versements:id_versement,id_convention,versement_montant,versement_date_reception,versement_type']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');
        $montantConventions = $projet->conventions->sum('convention_montant_fcfa');

        $canCloturer = false;
        $clotureBlockers = null;

        if ($projet->projet_statut === ProjectStatus::EnCours) {
            $clotureBlockers = $this->projetService->getBlockersCloture($projet);
            $canCloturer = $clotureBlockers === null;
        }

        return Inertia::render('daf/Projets/Show', [
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->projet_titre,
                'description' => $projet->projet_description,
                'objectifs' => $projet->projet_objectifs,
                'status' => $projet->projet_statut->value,
                'status_label' => $projet->projet_statut->label(),
                'statut_final' => $projet->statut_final?->value,
                'statut_final_label' => $projet->statut_final?->label(),
                'montant_estime' => $projet->projet_montant_estime,
                'montant_conventions' => $montantConventions,
                'total_versements' => $totalVersements,
                'date_debut' => $projet->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
                'porteur' => [
                    'nom' => $projet->porteur->name,
                    'email' => $projet->porteur->email,
                    'telephone' => $projet->porteur->telephone,
                ],
                'conventions' => $projet->conventions->map(fn (Convention $c) => [
                    'id' => $c->id,
                    'titre' => $c->convention_titre,
                    'bailleur' => ['nom' => $c->bailleur->bailleur_nom, 'sigle' => $c->bailleur->bailleur_sigle],
                    'montant_fcfa' => $c->convention_montant_fcfa,
                    'forme' => $c->convention_forme->value,
                    'forme_label' => $c->convention_forme->label(),
                    'status' => $c->convention_statut->value,
                    'status_label' => $c->convention_statut->label(),
                    'total_rubriques' => $c->rubriques->sum('rubrique_montant_prevu'),
                    'total_versements' => $c->versements->sum('versement_montant'),
                    'rubriques_count' => $c->rubriques->count(),
                    'versements_count' => $c->versements->count(),
                ]),
                'analyse_ecarts' => $this->buildAnalyseEcarts($projet),
                'can_cloturer' => $canCloturer,
                'cloture_blockers' => $clotureBlockers,
                'bilan_url' => $projet->projet_statut === ProjectStatus::Termine
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
            ProjetStatutFinal::from($request->validated('statut_final')),
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

        return $pdf->download("bilan-projet-{$projet->id}.pdf");
    }

    /** @return array<string, mixed> */
    private function buildAnalyseEcarts(Projet $projet): array
    {
        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');

        $totalDepenses = $projet->conventions->flatMap->rubriques->flatMap->demandesDepenses
            ->whereIn('demande_statut', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ])->sum('demande_montant');

        $budgetPrevu = $projet->conventions->sum('convention_montant_fcfa');
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
