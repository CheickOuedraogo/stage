<?php

namespace App\Http\Controllers\Ac;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

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
        ]);
    }

    public function exporterBilanPdf(Projet $projet): HttpResponse
    {
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))->setPaper('a4');

        return $pdf->download("bilan-projet-{$projet->id}.pdf");
    }
}
