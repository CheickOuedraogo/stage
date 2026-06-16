<?php

namespace App\Http\Controllers\AgentComptable;

use App\Enums\StatutFinalProjet;
use App\Enums\StatutProjet;
use App\Exports\BilanProjetExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Daf\CloturerProjetRequest;
use App\Models\Convention;
use App\Models\Paiement;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ProjetController extends Controller
{
    public function __construct(private readonly ProjetService $projetService) {}

    public function index(Request $request): Response
    {
        $query = Projet::with(['porteur:id_utilisateur,utilisateur_nom', 'conventions.versements'])
            ->withCount('conventions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('projet_titre', 'like', "%{$search}%")
                    ->orWhereHas('porteur', function ($qp) use ($search) {
                        $qp->where('utilisateur_nom', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('statut')) {
            $query->where('projet_statut', $request->input('statut'));
        }

        $projets = $query->latest()
            ->paginate(10)
            ->through(function (Projet $p) {
                $montantConvs = $p->conventions->sum(fn ($c) => $c->montant_fcfa);
                $totalConsomme = Paiement::sumForProjet($p->id_projet);
                $totalVersements = $p->montant_total_versements;

                return [
                    'id' => $p->id_projet,
                    'titre' => $p->projet_titre,
                    'porteur' => $p->porteur->utilisateur_nom,
                    'statut' => $p->projet_statut->value,
                    'libelle_statut' => $p->projet_statut->label(),
                    'montant_estime' => $p->projet_montant_estime,
                    'montant_conventions' => $montantConvs,
                    'total_versements' => $totalVersements,
                    'total_consomme' => $totalConsomme,
                    'conventions_count' => $p->conventions_count,
                    'taux_execution' => $montantConvs > 0 ? round(($totalConsomme / $montantConvs) * 100, 1) : 0,
                    'taux_financement' => $p->projet_montant_estime > 0 ? round(($montantConvs / $p->projet_montant_estime) * 100, 1) : 0,
                    'date_debut' => $p->projet_date_debut?->toDateString(),
                    'date_fin_prevue' => $p->projet_date_fin_prevue?->toDateString(),
                ];
            });

        $totalBudgetInitial = Projet::sum('projet_montant_estime');
        $totalConventions = Convention::all()->sum(fn ($c) => $c->montant_fcfa);
        $totalConsommeGlobal = Paiement::sum('paiement_montant');

        return Inertia::render('ac/Projets/Index', [
            'projets' => $projets,
            'filters' => $request->only(['search', 'statut']),
            'stats' => [
                'total' => Projet::count(),
                'en_cours' => Projet::where('projet_statut', StatutProjet::EnCours->value)->count(),
                'total_budget' => $totalBudgetInitial,
                'total_conventions' => $totalConventions,
                'total_consomme' => $totalConsommeGlobal,
                'taux_execution_global' => $totalConventions > 0
                    ? round(($totalConsommeGlobal / $totalConventions) * 100, 1)
                    : 0,
            ],
        ]);
    }

    public function show(Projet $projet): Response
    {
        $projet->load([
            'porteur:id_utilisateur,utilisateur_nom,utilisateur_email,utilisateur_telephone',
            'conventions' => fn ($q) => $q->with(['bailleur:id_bailleur,bailleur_nom,bailleur_sigle', 'rubriques:id_rubrique,id_convention,rubrique_libelle,rubrique_montant', 'versements:id_versement,id_convention,versement_montant,versement_date_reception']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');
        $montantConventions = $projet->conventions->sum('montant_fcfa');

        $canCloturer = false;
        $clotureBlockers = null;

        if ($projet->projet_statut === StatutProjet::EnCours) {
            $clotureBlockers = $this->projetService->getBlockersCloture($projet);
            $canCloturer = $clotureBlockers === null;
        }

        $canMettreEnCours = false;
        $mettreEnCoursBlockers = null;

        if ($projet->projet_statut === StatutProjet::EnAttenteFinancement) {
            $mettreEnCoursBlockers = $this->projetService->getBlockersMettreEnCours($projet);
            $canMettreEnCours = $mettreEnCoursBlockers === null;
        }

        return Inertia::render('ac/Projets/Show', [
            'projet' => [
                'id' => $projet->id_projet,
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
                    'id' => $c->id_convention,
                    'titre' => $c->convention_titre,
                    'bailleur' => ['nom' => $c->bailleur->bailleur_nom, 'sigle' => $c->bailleur->bailleur_sigle],
                    'montant_fcfa' => $c->montant_fcfa,
                    'forme' => $c->convention_forme->value,
                    'forme_label' => $c->convention_forme->label(),
                    'statut' => $c->convention_statut->value,
                    'libelle_statut' => $c->convention_statut->label(),
                    'total_rubriques' => $c->rubriques->sum('rubrique_montant'),
                    'total_versements' => $c->versements->sum('versement_montant'),
                    'rubriques_count' => $c->rubriques->count(),
                    'versements_count' => $c->versements->count(),
                ]),
                'analyse_ecarts' => $this->buildAnalyseEcarts($projet),
                'can_cloturer' => $canCloturer,
                'cloture_blockers' => $clotureBlockers,
                'can_mettre_en_cours' => $canMettreEnCours,
                'mettre_en_cours_blockers' => $mettreEnCoursBlockers,
                'bilan_url' => $projet->projet_statut === StatutProjet::Termine
                    ? route('ac.projets.bilan', $projet)
                    : null,
            ],
        ]);
    }

    public function mettreEnCours(Projet $projet): RedirectResponse
    {
        $projet->loadMissing('conventions');
        $this->projetService->mettreEnCours($projet, auth()->user());

        return redirect()->route('ac.projets.show', $projet)
            ->with('success', "Le projet « {$projet->projet_titre} » est maintenant en cours. Le porteur peut soumettre des demandes.");
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

        return redirect()->route('ac.projets.show', $projet)
            ->with('success', "Le projet « {$projet->projet_titre} » a été clôturé avec succès.");
    }

    public function bilan(Projet $projet): Response
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        return Inertia::render('daf/Projets/Bilan', [
            'bilan' => $bilan,
            'pdf_url' => route('ac.projets.bilan.pdf', $projet),
            'excel_url' => route('ac.projets.bilan.excel', $projet),
        ]);
    }

    public function exporterBilanPdf(Projet $projet): HttpResponse
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))->setPaper('a4', 'landscape');

        return $pdf->download("bilan-projet-{$projet->id_projet}.pdf");
    }

    public function exporterBilanExcel(Projet $projet): mixed
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        return Excel::download(
            new BilanProjetExport($bilan),
            "rapport-financier-{$projet->id_projet}.xlsx"
        );
    }

    /** @return array<string, mixed> */
    private function buildAnalyseEcarts(Projet $projet): array
    {
        $totalVersements = $projet->montant_total_versements;
        $totalDepenses = Paiement::sumForProjet($projet->id_projet);

        $budgetPrevu = $projet->conventions->sum(fn ($c) => $c->montant_fcfa);
        $ecartBudget = $budgetPrevu - $totalDepenses;

        $analyseDelais = $projet->analyse_delais;

        return [
            'budget_prevu' => $budgetPrevu,
            'total_versements' => $totalVersements,
            'total_depenses' => $totalDepenses,
            'ecart_budget' => $ecartBudget,
            'solde_caisse' => $totalVersements - $totalDepenses,
            'taux_execution' => $budgetPrevu > 0 ? round(($totalDepenses / $budgetPrevu) * 100, 1) : 0,
            'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
            'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
            'ecart_temps_jours' => $analyseDelais['jours'],
            'ecart_temps_label' => $analyseDelais['label'],
        ];
    }
}
