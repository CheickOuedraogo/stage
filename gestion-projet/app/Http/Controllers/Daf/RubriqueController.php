<?php

namespace App\Http\Controllers\Daf;

use App\Enums\DemandeStatus;
use App\Http\Controllers\Controller;
use App\Models\Rubrique;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RubriqueController extends Controller
{
    public function index(Request $request): Response
    {
        $rubriques = Rubrique::with([
            'convention:id,titre,projet_id,bailleur_id',
            'convention.projet:id,titre',
            'convention.bailleur:id,nom,sigle',
            'demandesDepenses' => fn ($q) => $q->whereIn('status', [
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
            ]),
        ])
            ->when($request->filled('search'), fn ($q) => $q->where(
                fn ($q2) => $q2->where('libelle', 'like', "%{$request->search}%")
                    ->orWhereHas('convention', fn ($q3) => $q3->where('titre', 'like', "%{$request->search}%"))
            ))
            ->orderBy('libelle')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Rubrique $r) => [
                'id' => $r->id,
                'libelle' => $r->libelle,
                'montant_prevu' => $r->montant_prevu,
                'consomme' => $r->demandesDepenses->sum('montant'),
                'disponible' => max(0, $r->montant_prevu - $r->demandesDepenses->sum('montant')),
                'taux' => $r->montant_prevu > 0
                    ? round(($r->demandesDepenses->sum('montant') / $r->montant_prevu) * 100)
                    : 0,
                'convention' => $r->convention->titre,
                'projet' => $r->convention->projet->titre,
                'bailleur' => $r->convention->bailleur->sigle ?? $r->convention->bailleur->nom,
                'convention_id' => $r->convention_id,
                'projet_id' => $r->convention->projet_id,
                'description' => $r->description,
            ]);

        $stats = [
            'total_prevu' => Rubrique::sum('montant_prevu'),
            'total_rubriques' => Rubrique::count(),
        ];

        return Inertia::render('daf/Rubriques/Index', [
            'rubriques' => $rubriques,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }
}
