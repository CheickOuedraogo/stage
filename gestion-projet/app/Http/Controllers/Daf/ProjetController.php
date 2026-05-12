<?php

namespace App\Http\Controllers\Daf;

use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
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
        $projets = Projet::with(['porteur:id,name', 'conventions:id,projet_id,montant_fcfa'])
            ->withCount('conventions')
            ->latest()
            ->get()
            ->map(fn (Projet $p) => [
                'id' => $p->id,
                'titre' => $p->titre,
                'porteur' => $p->porteur->name,
                'status' => $p->status->value,
                'status_label' => $p->status->label(),
                'montant_estime' => $p->montant_estime,
                'montant_conventions' => $p->conventions->sum('montant_fcfa'),
                'conventions_count' => $p->conventions_count,
                'date_debut' => $p->date_debut?->toDateString(),
                'date_fin_prevue' => $p->date_fin_prevue?->toDateString(),
            ]);

        return Inertia::render('daf/Projets/Index', [
            'projets' => $projets,
            'stats' => [
                'total' => Projet::count(),
                'en_cours' => Projet::where('status', 'en_cours')->count(),
                'total_budget' => Projet::sum('montant_estime'),
                'total_conventions' => Convention::sum('montant_fcfa'),
            ],
        ]);
    }

    public function show(Projet $projet): Response
    {
        $projet->load([
            'porteur:id,name,email,telephone',
            'conventions' => fn ($q) => $q->with(['bailleur:id,nom,sigle', 'rubriques:id,convention_id,libelle,montant_prevu', 'versements:id,convention_id,montant,date_reception,type']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('montant');
        $montantConventions = $projet->conventions->sum('montant_fcfa');

        $canCloturer = false;
        $clotureBlockers = null;

        if ($projet->status === ProjectStatus::EnCours) {
            $clotureBlockers = $this->projetService->getBlockersCloture($projet);
            $canCloturer = $clotureBlockers === null;
        }

        return Inertia::render('daf/Projets/Show', [
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->titre,
                'description' => $projet->description,
                'objectifs' => $projet->objectifs,
                'status' => $projet->status->value,
                'status_label' => $projet->status->label(),
                'montant_estime' => $projet->montant_estime,
                'montant_conventions' => $montantConventions,
                'total_versements' => $totalVersements,
                'date_debut' => $projet->date_debut?->toDateString(),
                'date_fin_prevue' => $projet->date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->date_fin_reelle?->toDateString(),
                'porteur' => [
                    'nom' => $projet->porteur->name,
                    'email' => $projet->porteur->email,
                    'telephone' => $projet->porteur->telephone,
                ],
                'conventions' => $projet->conventions->map(fn (Convention $c) => [
                    'id' => $c->id,
                    'titre' => $c->titre,
                    'bailleur' => ['nom' => $c->bailleur->nom, 'sigle' => $c->bailleur->sigle],
                    'montant_fcfa' => $c->montant_fcfa,
                    'forme' => $c->forme->value,
                    'forme_label' => $c->forme->label(),
                    'status' => $c->status->value,
                    'status_label' => $c->status->label(),
                    'total_rubriques' => $c->rubriques->sum('montant_prevu'),
                    'total_versements' => $c->versements->sum('montant'),
                    'rubriques_count' => $c->rubriques->count(),
                    'versements_count' => $c->versements->count(),
                ]),
                'analyse_ecarts' => $this->buildAnalyseEcarts($projet),
                'can_cloturer' => $canCloturer,
                'cloture_blockers' => $clotureBlockers,
                'bilan_url' => $projet->status === ProjectStatus::Termine
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
        $this->projetService->cloturer($projet, auth()->user(), $request->date('date_fin_reelle'));

        return back()->with('success', "Le projet « {$projet->titre} » a été clôturé avec succès.");
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
        $totalVersements = $projet->conventions->flatMap->versements->sum('montant');

        $totalDepenses = $projet->conventions->flatMap->rubriques->flatMap->demandesDepenses
            ->whereIn('status', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ])->sum('montant');

        $budgetPrevu = $projet->conventions->sum('montant_fcfa');
        $ecartBudget = $budgetPrevu - $totalDepenses;

        $ecartTemps = null;
        $ecartTempsLabel = null;

        if ($projet->date_fin_prevue) {
            $dateRef = $projet->date_fin_reelle ?? now();
            $ecartTemps = $projet->date_fin_prevue->diffInDays($dateRef, false);
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
            'date_fin_prevue' => $projet->date_fin_prevue?->toDateString(),
            'date_fin_reelle' => $projet->date_fin_reelle?->toDateString(),
            'ecart_temps_jours' => $ecartTemps,
            'ecart_temps_label' => $ecartTempsLabel,
        ];
    }
}
