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

        $projets = Projet::forPorteur($porteur->id_utilisateur)
            ->withCount('conventions')
            ->with(['conventions:id_convention,id_projet,convention_montant,convention_taux_conversion'])
            ->when($request->filled('search'), fn ($q) => $q->where('projet_titre', 'like', '%'.$request->search.'%'))
            ->when($request->filled('statut'), fn ($q) => $q->where('projet_statut', $request->statut))
            ->latest()
            ->get()
            ->map(fn (Projet $p) => [
                'id' => $p->id_utilisateur,
                'titre' => $p->projet_titre,
                'statut' => $p->projet_statut->value,
                'libelle_statut' => $p->projet_statut->label(),
                'montant_estime' => $p->projet_montant_estime,
                'date_debut' => $p->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $p->projet_date_fin_prevue?->toDateString(),
                'conventions_count' => $p->conventions_count,
                'montant_conventions' => $p->conventions->sum('montant_fcfa'),
                'pourcentage_financement' => $p->projet_montant_estime > 0
                    ? (int) min(100, round(($p->conventions->sum('montant_fcfa') / $p->projet_montant_estime) * 100))
                    : 0,
            ]);

        return Inertia::render('porteur/Projets/Index', [
            'projets' => $projets,
            'filters' => $request->only(['search', 'statut']),
        ]);
    }

    public function show(Request $request, Projet $projet): Response
    {
        abort_unless($projet->id_utilisateur_porteur === $request->user()->id_utilisateur, 403);

        $projet->load([
            'conventions' => fn ($q) => $q->with(['bailleur:id_bailleur,bailleur_nom,bailleur_sigle', 'versements:id_versement,id_convention,versement_montant,versement_date_reception']),
        ]);

        $totalVersements = $projet->conventions->flatMap->versements->sum('versement_montant');
        $montantConventions = $projet->conventions->sum('montant_fcfa');

        return Inertia::render('porteur/Projets/Show', [
            'projet' => [
                'id' => $projet->id_utilisateur,
                'titre' => $projet->projet_titre,
                'description' => $projet->projet_description,
                'objectifs' => $projet->projet_objectifs,
                'activites' => $projet->projet_activites,
                'statut' => $projet->projet_statut->value,
                'libelle_statut' => $projet->projet_statut->label(),
                'montant_estime' => $projet->projet_montant_estime,
                'montant_conventions' => $montantConventions,
                'total_versements' => $totalVersements,
                'pourcentage_financement' => $projet->projet_montant_estime > 0
                    ? (int) min(100, round(($montantConventions / $projet->projet_montant_estime) * 100))
                    : 0,
                'date_debut' => $projet->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
                'conventions' => $projet->conventions->map(fn (Convention $c) => [
                    'id' => $c->id_utilisateur,
                    'titre' => $c->convention_titre,
                    'bailleur' => ['nom' => $c->bailleur->bailleur_nom, 'sigle' => $c->bailleur->bailleur_sigle],
                    'montant_fcfa' => $c->montant_fcfa,
                    'forme' => $c->convention_forme->value,
                    'forme_label' => $c->convention_forme->label(),
                    'statut' => $c->convention_statut->value,
                    'libelle_statut' => $c->convention_statut->label(),
                    'date_fin' => $c->convention_date_fin?->toDateString(),
                    'total_versements' => $c->versements->sum('versement_montant'),
                    'versements_count' => $c->versements->count(),
                ]),
            ],
        ]);
    }

    public function showConvention(Request $request, Projet $projet, Convention $convention): Response
    {
        abort_unless($projet->id_utilisateur_porteur === $request->user()->id_utilisateur, 403);
        abort_unless($convention->id_utilisateur_projet === $projet->id_utilisateur, 404);

        $convention->load(['bailleur:id_bailleur,bailleur_nom,bailleur_sigle,bailleur_type,bailleur_pays', 'rubriques', 'versements']);

        $hasDemandeActive = $convention->demandesDepenses()->actif()->exists();

        return Inertia::render('porteur/Projets/Convention', [
            'projet' => [
                'id' => $projet->id_utilisateur,
                'titre' => $projet->projet_titre,
            ],
            'has_demande_active' => $hasDemandeActive,
            'convention' => [
                'id' => $convention->id_utilisateur,
                'titre' => $convention->convention_titre,
                'description' => $convention->convention_description,
                'montant' => $convention->convention_montant,
                'montant_fcfa' => $convention->montant_fcfa,
                'devise_origine' => $convention->convention_devise,
                'taux_conversion' => $convention->convention_taux_conversion,
                'forme' => $convention->convention_forme->value,
                'forme_label' => $convention->convention_forme->label(),
                'statut' => $convention->convention_statut->value,
                'libelle_statut' => $convention->convention_statut->label(),
                'date_signature' => $convention->convention_date_signature?->toDateString(),
                'date_debut' => $convention->convention_date_debut?->toDateString(),
                'date_fin' => $convention->convention_date_fin?->toDateString(),
                'bailleur' => [
                    'nom' => $convention->bailleur->bailleur_nom,
                    'sigle' => $convention->bailleur->bailleur_sigle,
                    'type' => $convention->bailleur->bailleur_type,
                    'pays' => $convention->bailleur->bailleur_pays,
                ],
                'total_rubriques' => $convention->rubriques->sum('rubrique_montant_prevu'),
                'total_versements' => $convention->versements->sum('versement_montant'),
                'rubriques' => $convention->rubriques->map(fn ($r) => [
                    'id' => $r->id_utilisateur,
                    'libelle' => $r->rubrique_libelle,
                    'montant_prevu' => $r->rubrique_montant_prevu,
                    'montant_depense' => 0,
                    'description' => $r->rubrique_description,
                ])->values(),
                'versements' => $convention->versements->sortByDesc('versement_date_reception')->map(fn ($v) => [
                    'id' => $v->id_utilisateur,
                    'montant' => $v->versement_montant,
                    'date_reception' => $v->versement_date_reception->toDateString(),
                    'reference' => $v->versement_reference,
                    'description' => $v->versement_description,
                ])->values(),
            ],
        ]);
    }

    public function bilan(Request $request, Projet $projet): Response
    {
        abort_unless($projet->id_utilisateur_porteur === $request->user()->id_utilisateur, 403);
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        return Inertia::render('daf/Projets/Bilan', [
            'bilan' => $bilan,
            'pdf_url' => route('porteur.projets.bilan.pdf', $projet),
        ]);
    }

    public function exporterBilanPdf(Request $request, Projet $projet): HttpResponse
    {
        abort_unless($projet->id_utilisateur_porteur === $request->user()->id_utilisateur, 403);
        $this->authorize('voirBilan', $projet);

        $bilan = $this->projetService->genererBilan($projet);

        $pdf = Pdf::loadView('pdf.bilan-projet', compact('bilan'))->setPaper('a4');

        return $pdf->download("bilan-projet-{$projet->id_utilisateur}.pdf");
    }
}
