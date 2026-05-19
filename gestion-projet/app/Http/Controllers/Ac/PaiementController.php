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
            'demande:id_demande,demande_objet,id_porteur,id_convention,id_rubrique',
            'demande.porteur:id_utilisateur,name',
            'demande.convention:id_convention,convention_titre,id_projet',
            'demande.convention.projet:id_projet,projet_titre',
            'demande.rubrique:id_rubrique,rubrique_libelle',
            'enregistrePar:id_utilisateur,name',
        ])
            ->when($request->filled('projet_id'), fn ($q) => $q->whereHas(
                'demande.convention',
                fn ($q2) => $q2->where('id_projet', $request->projet_id)
            ))
            ->when($request->filled('mode'), fn ($q) => $q->where('paiement_mode', $request->mode))
            ->when($request->filled('search'), fn ($q) => $q->whereHas(
                'demande',
                fn ($q2) => $q2->where('demande_objet', 'like', "%{$request->search}%")
            ))
            ->latest('paiement_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Paiement $p) => [
                'id' => $p->id,
                'montant' => $p->paiement_montant,
                'date_paiement' => $p->paiement_date->toDateString(),
                'mode_paiement' => $p->paiement_mode->value,
                'mode_paiement_label' => $p->paiement_mode->label(),
                'reference' => $p->paiement_reference,
                'objet' => $p->demande->demande_objet,
                'porteur' => $p->demande->porteur->name,
                'convention' => $p->demande->convention->convention_titre,
                'projet' => $p->demande->convention->projet->projet_titre,
                'rubrique' => $p->demande->rubrique->rubrique_libelle,
                'enregistre_par' => $p->enregistrePar->name,
            ]);

        $totalMontant = Paiement::when($request->filled('projet_id'), fn ($q) => $q->whereHas(
            'demande.convention',
            fn ($q2) => $q2->where('id_projet', $request->projet_id)
        ))
            ->when($request->filled('mode'), fn ($q) => $q->where('paiement_mode', $request->mode))
            ->sum('paiement_montant');

        return Inertia::render('ac/Paiements/Index', [
            'paiements' => $paiements,
            'total_montant' => $totalMontant,
            'filters' => $request->only(['projet_id', 'mode', 'search']),
        ]);
    }
}
