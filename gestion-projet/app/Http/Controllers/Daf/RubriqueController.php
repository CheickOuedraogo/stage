<?php

namespace App\Http\Controllers\Daf;

use App\Enums\StatutDemande;
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
            'convention:id_convention,convention_titre,id_projet,id_bailleur',
            'convention.projet:id_projet,projet_titre',
            'convention.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
            'demandesDepenses' => fn ($q) => $q->whereIn('demande_statut', [
                StatutDemande::Payee->value,
                StatutDemande::RapportSoumis->value,
                StatutDemande::Terminee->value,
            ]),
        ])
            ->when($request->filled('search'), fn ($q) => $q->where(
                fn ($q2) => $q2->where('rubrique_libelle', 'like', "%{$request->search}%")
                    ->orWhereHas('convention', fn ($q3) => $q3->where('convention_titre', 'like', "%{$request->search}%"))
            ))
            ->orderBy('rubrique_libelle')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Rubrique $r) => [
                'id' => $r->id_utilisateur,
                'libelle' => $r->rubrique_libelle,
                'montant_prevu' => $r->rubrique_montant_prevu,
                'consomme' => $r->demandesDepenses->sum('demande_montant'),
                'disponible' => max(0, $r->rubrique_montant_prevu - $r->demandesDepenses->sum('demande_montant')),
                'taux' => $r->rubrique_montant_prevu > 0
                    ? round(($r->demandesDepenses->sum('demande_montant') / $r->rubrique_montant_prevu) * 100)
                    : 0,
                'convention' => $r->convention->convention_titre,
                'projet' => $r->convention->projet->projet_titre,
                'bailleur' => $r->convention->bailleur->bailleur_sigle ?? $r->convention->bailleur->bailleur_nom,
                'convention_id' => $r->id_utilisateur_convention,
                'projet_id' => $r->convention->id_utilisateur_projet,
                'description' => $r->rubrique_description,
            ]);

        $stats = [
            'total_prevu' => Rubrique::sum('rubrique_montant_prevu'),
            'total_rubriques' => Rubrique::count(),
        ];

        return Inertia::render('daf/Rubriques/Index', [
            'rubriques' => $rubriques,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }
}
