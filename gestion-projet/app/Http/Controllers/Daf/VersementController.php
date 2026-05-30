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
            'convention:id_convention,convention_titre,id_projet,id_bailleur',
            'convention.projet:id_projet,projet_titre',
            'convention.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
        ])
            ->when($request->filled('search'), fn ($q) => $q->where(
                fn ($q2) => $q2->where('versement_reference', 'like', "%{$request->search}%")
                    ->orWhereHas('convention', fn ($q3) => $q3->where('convention_titre', 'like', "%{$request->search}%"))
            ))
            ->latest('versement_date_reception')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (Versement $v) => [
                'id' => $v->id_versement,
                'montant' => $v->versement_montant,
                'date_reception' => $v->versement_date_reception->toDateString(),
                'reference' => $v->versement_reference,
                'description' => $v->versement_description,
                'convention' => $v->convention->convention_titre,
                'projet' => $v->convention->projet->projet_titre,
                'bailleur' => $v->convention->bailleur->bailleur_sigle ?? $v->convention->bailleur->bailleur_nom,
                'convention_id' => $v->id_convention,
                'projet_id' => $v->convention->id_projet,
            ]);

        $totalMontant = Versement::sum('versement_montant');

        return Inertia::render('daf/Versements/Index', [
            'versements' => $versements,
            'total_montant' => $totalMontant,
            'filters' => $request->only(['search']),
        ]);
    }
}
