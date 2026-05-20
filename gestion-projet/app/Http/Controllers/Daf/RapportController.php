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
        $projets = Projet::orderBy('projet_titre')
            ->get(['id', 'projet_titre', 'projet_statut'])
            ->map(fn (Projet $p) => [
                'id' => $p->id,
                'titre' => $p->projet_titre,
                'status' => $p->projet_statut->value,
                'status_label' => $p->projet_statut->label(),
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
            'conventions.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q->whereIn('demande_statut', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ]),
            'conventions.versements',
        ])->findOrFail($request->projet_id);

        $rubriques = $projet->conventions->flatMap(fn ($c) => $c->rubriques->map(fn ($r) => [
            'libelle' => $r->rubrique_libelle,
            'convention' => "{$c->convention_titre} / {$c->bailleur->bailleur_sigle}",
            'montant_prevu' => $r->rubrique_montant_prevu,
            'consomme' => $r->demandesDepenses->sum('demande_montant'),
        ]))->values()->toArray();

        $totalVersions = $projet->conventions->flatMap->versements->sum('versement_montant');
        $totalDepenses = array_sum(array_column($rubriques, 'consomme'));

        $data = [
            'projets' => [[
                'titre' => $projet->projet_titre,
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
            new ExecutionBudgetaireExport($rubriques, $projet->projet_titre),
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
            'porteur:id_utilisateur,name',
            'conventions.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
            'conventions.versements',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q->whereIn('demande_statut', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ]),
        ])->findOrFail($request->projet_id);

        $totalVersions = $projet->conventions->flatMap->versements->sum('versement_montant');
        $totalDepenses = $projet->conventions->flatMap->rubriques->flatMap->demandesDepenses->sum('demande_montant');
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

        $projetData = [
            'titre' => $projet->projet_titre,
            'porteur' => $projet->porteur->name,
            'status_label' => $projet->projet_statut->label(),
            'date_debut' => $projet->projet_date_debut?->format('d/m/Y'),
            'date_fin_prevue' => $projet->projet_date_fin_prevue?->format('d/m/Y'),
            'date_fin_reelle' => $projet->projet_date_fin_reelle?->format('d/m/Y'),
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
            'bailleur' => $c->bailleur->bailleur_nom,
            'forme_label' => $c->convention_forme->label(),
            'montant_fcfa' => $c->montant_fcfa,
            'total_versements' => $c->versements->sum('versement_montant'),
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
