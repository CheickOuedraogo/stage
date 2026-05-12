<?php

namespace App\Http\Controllers\Ac;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaiementController extends Controller
{
    public function index(Request $request): Response
    {
        $paiements = Paiement::with([
            'demande:id,objet,porteur_id,convention_id,rubrique_id',
            'demande.porteur:id,name',
            'demande.convention:id,titre,projet_id',
            'demande.convention.projet:id,titre',
            'demande.rubrique:id,libelle',
            'enregistrePar:id,name',
        ])
            ->when($request->filled('projet_id'), fn ($q) => $q->whereHas(
                'demande.convention',
                fn ($q2) => $q2->where('projet_id', $request->projet_id)
            ))
            ->when($request->filled('mode'), fn ($q) => $q->where('mode_paiement', $request->mode))
            ->when($request->filled('search'), fn ($q) => $q->whereHas(
                'demande',
                fn ($q2) => $q2->where('objet', 'like', "%{$request->search}%")
            ))
            ->latest('date_paiement')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Paiement $p) => [
                'id' => $p->id,
                'montant' => $p->montant,
                'date_paiement' => $p->date_paiement->toDateString(),
                'mode_paiement' => $p->mode_paiement->value,
                'mode_paiement_label' => $p->mode_paiement->label(),
                'reference' => $p->reference,
                'objet' => $p->demande->objet,
                'porteur' => $p->demande->porteur->name,
                'convention' => $p->demande->convention->titre,
                'projet' => $p->demande->convention->projet->titre,
                'rubrique' => $p->demande->rubrique->libelle,
                'enregistre_par' => $p->enregistrePar->name,
            ]);

        $totalMontant = Paiement::when($request->filled('projet_id'), fn ($q) => $q->whereHas(
            'demande.convention',
            fn ($q2) => $q2->where('projet_id', $request->projet_id)
        ))
            ->when($request->filled('mode'), fn ($q) => $q->where('mode_paiement', $request->mode))
            ->sum('montant');

        return Inertia::render('ac/Paiements/Index', [
            'paiements' => $paiements,
            'total_montant' => $totalMontant,
            'filters' => $request->only(['projet_id', 'mode', 'search']),
        ]);
    }
}
