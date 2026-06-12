<?php

namespace App\Http\Controllers\Daf;

use App\Exports\BilanProjetExport;
use App\Exports\ExecutionBudgetaireExport;
use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class RapportController extends Controller
{
    public function __construct(private readonly ProjetService $projetService) {}

    public function index(Request $request): Response
    {
        $projets = Projet::orderBy('projet_titre')
            ->get(['id_projet', 'projet_titre', 'projet_statut'])
            ->map(fn (Projet $p) => [
                'id' => $p->id_projet,
                'titre' => $p->projet_titre,
                'statut' => $p->projet_statut->value,
                'libelle_statut' => $p->projet_statut->label(),
            ]);

        return Inertia::render('daf/Rapports/Index', [
            'projets' => $projets,
        ]);
    }

    public function executionBudgetaire(Request $request): mixed
    {
        $request->validate([
            'projet_id' => ['required', 'exists:projets,id_projet'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $projet = Projet::with([
            'conventions.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
            'conventions.rubriques',
            'conventions.versements',
        ])->findOrFail($request->projet_id);

        $rubriques = $projet->conventions->flatMap(fn ($c) => $c->rubriques->map(fn ($r) => [
            'libelle' => $r->rubrique_libelle,
            'convention' => "{$c->convention_titre} / {$c->bailleur->bailleur_sigle}",
            'montant_prevu' => $r->rubrique_montant,
            'consomme' => Paiement::sumForRubrique($r->id_rubrique),
        ]))->values()->toArray();

        $totalVersions = $projet->conventions->flatMap->versements->sum('versement_montant');
        $totalDepenses = Paiement::sumForProjet($projet->id_projet);

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

            return $pdf->download("execution_budgetaire_{$projet->id_projet}.pdf");
        }

        return Excel::download(
            new ExecutionBudgetaireExport($rubriques, $projet->projet_titre),
            "execution_budgetaire_{$projet->id_projet}.xlsx"
        );
    }

    public function cloture(Request $request): mixed
    {
        $request->validate([
            'projet_id' => ['required', 'exists:projets,id_projet'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $projet = Projet::findOrFail($request->projet_id);
        $bilan = $this->projetService->genererBilan($projet);

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))
                ->setPaper('a4', 'landscape');

            return $pdf->download("cloture_{$projet->id_projet}.pdf");
        }

        return Excel::download(
            new BilanProjetExport($bilan),
            "cloture_{$projet->id_projet}.xlsx"
        );
    }
}
