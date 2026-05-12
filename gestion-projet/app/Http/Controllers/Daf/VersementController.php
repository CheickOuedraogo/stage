<?php

namespace App\Http\Controllers\Daf;

use App\Http\Controllers\Controller;
use App\Models\Versement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VersementController extends Controller
{
    public function index(Request $request): Response
    {
        $versements = Versement::with([
            'convention:id,titre,projet_id,bailleur_id',
            'convention.projet:id,titre',
            'convention.bailleur:id,nom,sigle',
        ])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), fn ($q) => $q->where(
                fn ($q2) => $q2->where('reference', 'like', "%{$request->search}%")
                    ->orWhereHas('convention', fn ($q3) => $q3->where('titre', 'like', "%{$request->search}%"))
            ))
            ->latest('date_reception')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Versement $v) => [
                'id' => $v->id,
                'montant' => $v->montant,
                'date_reception' => $v->date_reception->toDateString(),
                'type' => $v->type->value,
                'type_label' => $v->type->label(),
                'reference' => $v->reference,
                'description' => $v->description,
                'convention' => $v->convention->titre,
                'projet' => $v->convention->projet->titre,
                'bailleur' => $v->convention->bailleur->sigle ?? $v->convention->bailleur->nom,
                'convention_id' => $v->convention_id,
                'projet_id' => $v->convention->projet_id,
            ]);

        $totalMontant = Versement::when($request->filled('type'), fn ($q) => $q->where('type', $request->type))->sum('montant');

        return Inertia::render('daf/Versements/Index', [
            'versements' => $versements,
            'total_montant' => $totalMontant,
            'filters' => $request->only(['type', 'search']),
        ]);
    }
}
