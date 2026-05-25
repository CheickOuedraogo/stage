<?php

namespace App\Services;

use App\Enums\StatutConvention;
use App\Enums\StatutDemande;
use App\Enums\StatutFinalProjet;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\JournalAudit;
use App\Models\Notification;
use App\Models\Projet;
use App\Models\Utilisateur;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjetService
{
    /**
     * Returns a human-readable blocker message, or null if the project can be closed.
     */
    public function getBlockersCloture(Projet $projet): ?string
    {
        if ($projet->projet_statut !== StatutProjet::EnCours) {
            return "Statut actuel : {$projet->projet_statut->label()}. Seuls les projets en cours peuvent être clôturés.";
        }

        $conventionIds = $projet->conventions->pluck('id_convention');

        $demandesActives = DemandeDepense::whereIn('id_convention', $conventionIds)
            ->whereNotIn('demande_statut', [
                StatutDemande::Terminee->value,
                StatutDemande::RejeteeDaf->value,
                StatutDemande::RejeteeAgentComptable->value,
            ])
            ->count();

        if ($demandesActives > 0) {
            return "{$demandesActives} demande(s) de dépense encore en cours de traitement.";
        }

        $conventionsActives = $projet->conventions->filter(
            fn (Convention $c) => ! in_array($c->convention_statut, [
                StatutConvention::Terminee,
                StatutConvention::Annulee,
            ])
        )->count();

        if ($conventionsActives > 0) {
            return "{$conventionsActives} convention(s) encore active(s) ou suspendue(s). Clôturez-les d'abord.";
        }

        return null;
    }

    /**
     * Throws a ValidationException if the project cannot be closed.
     */
    public function verifierConditionsCloture(Projet $projet): void
    {
        $blocker = $this->getBlockersCloture($projet);

        if ($blocker !== null) {
            throw ValidationException::withMessages(['projet' => $blocker]);
        }
    }

    /**
     * Closes the project: updates status, date, final outcome, logs to audit, and notifies the porteur.
     */
    public function cloturer(Projet $projet, Utilisateur $daf, CarbonInterface $dateFinReelle, StatutFinalProjet $statutFinal): void
    {
        DB::transaction(function () use ($projet, $daf, $dateFinReelle, $statutFinal): void {
            $projet->update([
                'projet_statut' => StatutProjet::Termine,
                'projet_date_fin_reelle' => $dateFinReelle,
                'statut_final' => $statutFinal,
            ]);

            JournalAudit::log(
                'projet_cloture',
                $projet,
                description: "Clôture du projet « {$projet->projet_titre} » par {$daf->utilisateur_nom}",
            );

            $projet->loadMissing('porteur');
            Notification::pourProjetCloture($projet->id_porteur, $projet);
        });
    }

    /**
     * Generates the structured closure report data.
     *
     * @return array{
     *   projet: array<string, mixed>,
     *   conventions: list<array<string, mixed>>,
     *   demandes: list<array<string, mixed>>,
     *   paiements_directs: list<array<string, mixed>>,
     *   analyse_ecarts: array<string, mixed>,
     * }
     */
    public function genererBilan(Projet $projet): array
    {
        $projet->loadMissing([
            'porteur:id_utilisateur,name',
            'conventions.bailleur:id_bailleur,bailleur_nom,bailleur_sigle',
            'conventions.versements',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q
                ->where('demande_statut', StatutDemande::Terminee->value)
                ->with('paiement'),
            'conventions.paiementsDirects.rubrique:id_rubrique,rubrique_libelle',
        ]);

        $conventions = $projet->conventions->map(fn (Convention $c) => [
            'id' => $c->id_utilisateur,
            'titre' => $c->convention_titre,
            'bailleur' => $c->bailleur->bailleur_nom,
            'bailleur_sigle' => $c->bailleur->bailleur_sigle,
            'montant_fcfa' => $c->montant_fcfa,
            'total_versements' => $c->versements->sum('versement_montant'),
            'total_depenses' => $c->rubriques->flatMap->demandesDepenses->sum('demande_montant'),
            'total_paiements_directs' => $c->paiementsDirects->sum('paiement_montant'),
            'solde' => $c->montant_fcfa - $c->versements->sum('versement_montant'),
        ])->values();

        $demandes = $projet->conventions->flatMap(fn (Convention $c) => $c->rubriques->flatMap(fn ($r) => $r->demandesDepenses->map(fn (DemandeDepense $d) => [
            'objet' => $d->demande_objet,
            'montant' => $d->demande_montant,
            'rubrique' => $r->rubrique_libelle,
            'convention' => $c->bailleur->bailleur_sigle ?? $c->bailleur->bailleur_nom,
            'date_paiement' => $d->paiement?->paiement_date?->toDateString(),
        ])
        )
        )->values();

        $paiementsDirects = $projet->conventions->flatMap(fn (Convention $c) => $c->paiementsDirects->map(fn ($p) => [
            'objet' => $p->paiement_objet,
            'montant' => $p->paiement_montant,
            'rubrique' => $p->rubrique?->rubrique_libelle,
            'convention' => $c->bailleur->bailleur_sigle ?? $c->bailleur->bailleur_nom,
            'date_paiement' => $p->paiement_date?->toDateString(),
        ])
        )->values();

        $budgetPrevu = $projet->conventions->sum('montant_fcfa');
        $totalVersements = $conventions->sum('total_versements');
        $totalDepenses = $conventions->sum('total_depenses') + $conventions->sum('total_paiements_directs');

        $ecartTemps = null;
        $ecartTempsLabel = null;

        if ($projet->projet_date_fin_prevue) {
            $dateRef = $projet->projet_date_fin_reelle ?? now();
            $ecartTemps = (int) $projet->projet_date_fin_prevue->diffInDays($dateRef, false);
            $ecartTempsLabel = $ecartTemps > 0
                ? "{$ecartTemps} jour(s) de retard"
                : ($ecartTemps < 0 ? abs($ecartTemps).' jour(s) d\'avance' : 'Dans les délais');
        }

        return [
            'projet' => [
                'id' => $projet->id_utilisateur,
                'titre' => $projet->projet_titre,
                'porteur' => $projet->porteur->utilisateur_nom,
                'statut' => $projet->projet_statut->value,
                'libelle_statut' => $projet->projet_statut->label(),
                'statut_final' => $projet->statut_final?->value,
                'statut_final_label' => $projet->statut_final?->label(),
                'date_debut' => $projet->projet_date_debut?->toDateString(),
                'date_fin_prevue' => $projet->projet_date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->projet_date_fin_reelle?->toDateString(),
            ],
            'conventions' => $conventions->all(),
            'demandes' => $demandes->all(),
            'paiements_directs' => $paiementsDirects->all(),
            'analyse_ecarts' => [
                'budget_initial' => $projet->projet_montant_estime,
                'budget_prevu' => $budgetPrevu,
                'total_versements' => $totalVersements,
                'total_depenses' => $totalDepenses,
                'ecart_budget' => $budgetPrevu - $totalDepenses,
                'taux_execution' => $budgetPrevu > 0 ? round(($totalDepenses / $budgetPrevu) * 100, 1) : 0,
                'conventions_depassent_budget_initial' => $budgetPrevu > $projet->projet_montant_estime,
                'ecart_temps_jours' => $ecartTemps,
                'ecart_temps_label' => $ecartTempsLabel,
            ],
        ];
    }
}
