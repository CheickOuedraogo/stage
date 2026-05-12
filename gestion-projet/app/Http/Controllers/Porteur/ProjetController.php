<?php

namespace App\Http\Controllers\Porteur;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Projet;
use App\Services\ProjetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjetController extends Controller
{
    public function __construct(private readonly ProjetService $projetService) {}

    public function index(Request $request): Response
    {
        $porteur = $request->user();

        $projets = Projet::forPorteur($porteur->id)
            ->withCount('conventions')
            ->with(['conventions:id,projet_id,montant_fcfa'])
            ->when($request->filled('search'), fn ($q) => $q->where('titre', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get()
            ->map(fn (Projet $p) => [
                'id' => $p->id,
                'titre' => $p->titre,
                'status' => $p->status->value,
                'status_label' => $p->status->label(),
                'montant_estime' => $p->montant_estime,
                'date_debut' => $p->date_debut?->toDateString(),
                'date_fin_prevue' => $p->date_fin_prevue?->toDateString(),
                'conventions_count' => $p->conventions_count,
                'montant_conventions' => $p->conventions->sum('montant_fcfa'),
                'pourcentage_financement' => $p->montant_estime > 0
                    ? (int) min(100, round(($p->conventions->sum('montant_fcfa') / $p->montant_estime) * 100))
                    : 0,
            ]);

        return Inertia::render('porteur/Projets/Index', [
            'projets' => $projets,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(Request $request, Projet $projet): Response
    {
        abort_unless($projet->porteur_id === $request->user()->id, 403);

        $projet->load([
            'conventions' => fn ($q) => $q->with(['bailleur:id,nom,sigle', 'versements:id,convention_id,montant,date_reception,type']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('montant');
        $montantConventions = $projet->conventions->sum('montant_fcfa');

        return Inertia::render('porteur/Projets/Show', [
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->titre,
                'description' => $projet->description,
                'objectifs' => $projet->objectifs,
                'activites' => $projet->activites,
                'status' => $projet->status->value,
                'status_label' => $projet->status->label(),
                'montant_estime' => $projet->montant_estime,
                'montant_conventions' => $montantConventions,
                'total_versements' => $totalVersements,
                'pourcentage_financement' => $projet->montant_estime > 0
                    ? (int) min(100, round(($montantConventions / $projet->montant_estime) * 100))
                    : 0,
                'date_debut' => $projet->date_debut?->toDateString(),
                'date_fin_prevue' => $projet->date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->date_fin_reelle?->toDateString(),
                'conventions' => $projet->conventions->map(fn (Convention $c) => [
                    'id' => $c->id,
                    'titre' => $c->titre,
                    'bailleur' => ['nom' => $c->bailleur->nom, 'sigle' => $c->bailleur->sigle],
                    'montant_fcfa' => $c->montant_fcfa,
                    'forme' => $c->forme->value,
                    'forme_label' => $c->forme->label(),
                    'status' => $c->status->value,
                    'status_label' => $c->status->label(),
                    'date_fin' => $c->date_fin?->toDateString(),
                    'total_versements' => $c->versements->sum('montant'),
                    'versements_count' => $c->versements->count(),
                ]),
            ],
        ]);
    }

    public function showConvention(Request $request, Projet $projet, Convention $convention): Response
    {
        abort_unless($projet->porteur_id === $request->user()->id, 403);
        abort_unless($convention->projet_id === $projet->id, 404);

        $convention->load(['bailleur:id,nom,sigle,type,pays', 'rubriques', 'versements']);

        $hasDemandeActive = $convention->demandesDepenses()->active()->exists();

        return Inertia::render('porteur/Projets/Convention', [
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->titre,
            ],
            'has_demande_active' => $hasDemandeActive,
            'convention' => [
                'id' => $convention->id,
                'titre' => $convention->titre,
                'description' => $convention->description,
                'montant' => $convention->montant,
                'montant_fcfa' => $convention->montant_fcfa,
                'devise_origine' => $convention->devise_origine,
                'taux_conversion' => $convention->taux_conversion,
                'forme' => $convention->forme->value,
                'forme_label' => $convention->forme->label(),
                'status' => $convention->status->value,
                'status_label' => $convention->status->label(),
                'date_signature' => $convention->date_signature?->toDateString(),
                'date_debut' => $convention->date_debut?->toDateString(),
                'date_fin' => $convention->date_fin?->toDateString(),
                'bailleur' => [
                    'nom' => $convention->bailleur->nom,
                    'sigle' => $convention->bailleur->sigle,
                    'type' => $convention->bailleur->type,
                    'pays' => $convention->bailleur->pays,
                ],
                'total_rubriques' => $convention->rubriques->sum('montant_prevu'),
                'total_versements' => $convention->versements->sum('montant'),
                'rubriques' => $convention->rubriques->map(fn ($r) => [
                    'id' => $r->id,
                    'libelle' => $r->libelle,
                    'montant_prevu' => $r->montant_prevu,
                    'montant_depense' => 0,
                    'description' => $r->description,
                ])->values(),
                'versements' => $convention->versements->sortByDesc('date_reception')->map(fn ($v) => [
                    'id' => $v->id,
                    'montant' => $v->montant,
                    'date_reception' => $v->date_reception->toDateString(),
                    'type' => $v->type->value,
                    'type_label' => $v->type->label(),
                    'reference' => $v->reference,
                    'description' => $v->description,
                ])->values(),
            ],
        ]);
    }

    public function bilan(Request $request, Projet $projet): Response
    {
        abort_unless($projet->porteur_id === $request->user()->id, 403);
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        return Inertia::render('daf/Projets/Bilan', [
            'bilan' => $bilan,
            'pdf_url' => route('porteur.projets.bilan.pdf', $projet),
        ]);
    }

    public function exporterBilanPdf(Request $request, Projet $projet): HttpResponse
    {
        abort_unless($projet->porteur_id === $request->user()->id, 403);
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))->setPaper('a4');

        return $pdf->download("bilan-projet-{$projet->id}.pdf");
    }
}
