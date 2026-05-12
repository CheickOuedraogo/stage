<?php

namespace App\Http\Controllers\Daf;

use App\Enums\DemandeStatus;
use App\Exports\ClotureProjetExport;
use App\Exports\ExecutionBudgetaireExport;
use App\Http\Controllers\Controller;
use App\Models\Projet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class RapportController extends Controller
{
    public function index(Request $request): Response
    {
        $projets = Projet::orderBy('titre')
            ->get(['id', 'titre', 'status'])
            ->map(fn (Projet $p) => [
                'id' => $p->id,
                'titre' => $p->titre,
                'status' => $p->status->value,
                'status_label' => $p->status->label(),
            ]);

        return Inertia::render('daf/Rapports/Index', [
            'projets' => $projets,
        ]);
    }

    public function executionBudgetaire(Request $request): mixed
    {
        $request->validate([
            'projet_id' => ['required', 'exists:projets,id'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $projet = Projet::with([
            'conventions.bailleur:id,nom,sigle',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q->whereIn('status', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ]),
            'conventions.versements',
        ])->findOrFail($request->projet_id);

        $rubriques = $projet->conventions->flatMap(fn ($c) => $c->rubriques->map(fn ($r) => [
            'libelle' => $r->libelle,
            'convention' => "{$c->titre} / {$c->bailleur->sigle}",
            'montant_prevu' => $r->montant_prevu,
            'consomme' => $r->demandesDepenses->sum('montant'),
        ]))->values()->toArray();

        $totalVersions = $projet->conventions->flatMap->versements->sum('montant');
        $totalDepenses = array_sum(array_column($rubriques, 'consomme'));

        $data = [
            'projets' => [[
                'titre' => $projet->titre,
                'budget_prevu' => $projet->conventions->sum('montant_fcfa'),
                'total_versements' => $totalVersions,
                'total_depenses' => $totalDepenses,
                'rubriques' => $rubriques,
            ]],
        ];

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('rapports.execution_budgetaire', $data)->setPaper('a4');

            return $pdf->download("execution_budgetaire_{$projet->id}.pdf");
        }

        return Excel::download(
            new ExecutionBudgetaireExport($rubriques, $projet->titre),
            "execution_budgetaire_{$projet->id}.xlsx"
        );
    }

    public function cloture(Request $request): mixed
    {
        $request->validate([
            'projet_id' => ['required', 'exists:projets,id'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $projet = Projet::with([
            'porteur:id,name',
            'conventions.bailleur:id,nom,sigle',
            'conventions.versements',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q->whereIn('status', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ]),
        ])->findOrFail($request->projet_id);

        $totalVersions = $projet->conventions->flatMap->versements->sum('montant');
        $totalDepenses = $projet->conventions->flatMap->rubriques->flatMap->demandesDepenses->sum('montant');
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

        $projetData = [
            'titre' => $projet->titre,
            'porteur' => $projet->porteur->name,
            'status_label' => $projet->status->label(),
            'date_debut' => $projet->date_debut?->format('d/m/Y'),
            'date_fin_prevue' => $projet->date_fin_prevue?->format('d/m/Y'),
            'date_fin_reelle' => $projet->date_fin_reelle?->format('d/m/Y'),
        ];

        $analyse = [
            'budget_prevu' => $budgetPrevu,
            'total_versements' => $totalVersions,
            'total_depenses' => $totalDepenses,
            'ecart_budget' => $ecartBudget,
            'taux_execution' => $budgetPrevu > 0 ? round(($totalDepenses / $budgetPrevu) * 100, 1) : 0,
            'ecart_temps_jours' => $ecartTemps,
            'ecart_temps_label' => $ecartTempsLabel,
        ];

        $conventions = $projet->conventions->map(fn ($c) => [
            'bailleur' => $c->bailleur->nom,
            'forme_label' => $c->forme->label(),
            'montant_fcfa' => $c->montant_fcfa,
            'total_versements' => $c->versements->sum('montant'),
        ])->toArray();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('rapports.cloture_projet', compact('projet', 'analyse', 'conventions'))
                ->setPaper('a4');

            return $pdf->download("cloture_{$projet->id}.pdf");
        }

        return Excel::download(
            new ClotureProjetExport($projetData, $analyse, $conventions),
            "cloture_{$projet->id}.xlsx"
        );
    }
}
