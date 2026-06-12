<?php

namespace App\Http\Controllers\AgentComptable;

use App\Exports\BilanProjetExport;
use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ProjetController extends Controller
{
    public function __construct(private readonly ProjetService $projetService) {}

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
}
