<?php

namespace App\Http\Controllers\AgentComptable;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Paiement;
use App\Models\Projet;
use Inertia\Inertia;
use Inertia\Response;

class ConventionController extends Controller
{
    public function show(Projet $projet, Convention $convention): Response
    {
        abort_unless($convention->id_projet === $projet->id_projet, 404);

        $convention->load([
            'bailleur:id_bailleur,bailleur_nom,bailleur_sigle,bailleur_type,bailleur_pays',
            'rubriques',
            'versements' => fn ($q) => $q->orderByDesc('versement_date_reception'),
            'paiementsDirects' => fn ($q) => $q->with('rubrique:id_rubrique,rubrique_libelle')->orderByDesc('paiement_date'),
        ]);

        return Inertia::render('ac/Projets/Convention', [
            'projet' => ['id' => $projet->id_projet, 'titre' => $projet->projet_titre],
            'convention' => $this->formatConvention($convention),
        ]);
    }

    private function formatConvention(Convention $convention): array
    {
        return [
            'id' => $convention->id_convention,
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
            'total_rubriques' => $convention->rubriques->sum('rubrique_montant'),
            'total_versements' => $convention->versements->sum('versement_montant'),
            'rubriques' => $convention->rubriques->map(fn ($r) => [
                'id' => $r->id_rubrique,
                'libelle' => $r->rubrique_libelle,
                'montant_prevu' => $r->rubrique_montant,
                'montant_depense' => Paiement::sumForRubrique($r->id_rubrique),
                'description' => $r->rubrique_description,
            ])->values(),
            'versements' => $convention->versements->map(fn ($v) => [
                'id' => $v->id_versement,
                'montant' => $v->versement_montant,
                'date_reception' => $v->versement_date_reception?->toDateString(),
                'reference' => $v->versement_reference,
                'description' => $v->versement_description,
            ])->values(),
            'paiements_directs' => $convention->paiementsDirects->map(fn ($p) => [
                'id' => $p->id_paiement,
                'montant' => $p->paiement_montant,
                'objet_depense' => $p->paiement_objet,
                'date_paiement' => $p->paiement_date?->toDateString(),
                'rubrique' => $p->rubrique ? ['libelle' => $p->rubrique->rubrique_libelle] : null,
            ])->values(),
        ];
    }
}
