<?php

namespace App\Http\Controllers\Daf;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Projet;
use Inertia\Inertia;
use Inertia\Response;

class ProjetController extends Controller
{
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
            ],
        ]);
    }
}
