<?php

namespace App\Services;

use App\Enums\ConventionStatus;
use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
use App\Models\AuditLog;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Projet;
use App\Models\User;
use App\Notifications\ProjetCloture;
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
        if ($projet->status !== ProjectStatus::EnCours) {
            return "Statut actuel : {$projet->status->label()}. Seuls les projets en cours peuvent être clôturés.";
        }

        $conventionIds = $projet->conventions->pluck('id');

        $demandesActives = DemandeDepense::whereIn('convention_id', $conventionIds)
            ->whereNotIn('status', [
                DemandeStatus::Terminee->value,
                DemandeStatus::RejetéeDaf->value,
                DemandeStatus::RejetéeAc->value,
            ])
            ->count();

        if ($demandesActives > 0) {
            return "{$demandesActives} demande(s) de dépense encore en cours de traitement.";
        }

        $conventionsActives = $projet->conventions->filter(
            fn (Convention $c) => ! in_array($c->status, [
                ConventionStatus::Terminee,
                ConventionStatus::Annulee,
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
     * Closes the project: updates status, date, logs to audit, and notifies the porteur.
     */
    public function cloturer(Projet $projet, User $daf, CarbonInterface $dateFinReelle): void
    {
        DB::transaction(function () use ($projet, $daf, $dateFinReelle): void {
            $projet->update([
                'status' => ProjectStatus::Termine,
                'date_fin_reelle' => $dateFinReelle,
            ]);

            AuditLog::log(
                'projet_cloture',
                $projet,
                description: "Clôture du projet « {$projet->titre} » par {$daf->name}",
            );

            $projet->loadMissing('porteur');
            $projet->porteur->notify(new ProjetCloture($projet));
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
            'porteur:id,name',
            'conventions.bailleur:id,nom,sigle',
            'conventions.versements',
            'conventions.rubriques.demandesDepenses' => fn ($q) => $q
                ->where('status', DemandeStatus::Terminee->value)
                ->with('paiement'),
            'conventions.paiementsDirects.rubrique:id,libelle',
        ]);

        $conventions = $projet->conventions->map(fn (Convention $c) => [
            'id' => $c->id,
            'titre' => $c->titre,
            'bailleur' => $c->bailleur->nom,
            'bailleur_sigle' => $c->bailleur->sigle,
            'montant_fcfa' => $c->montant_fcfa,
            'total_versements' => $c->versements->sum('montant'),
            'total_depenses' => $c->rubriques->flatMap->demandesDepenses->sum('montant'),
            'total_paiements_directs' => $c->paiementsDirects->sum('montant'),
            'solde' => $c->montant_fcfa - $c->versements->sum('montant'),
        ])->values();

        $demandes = $projet->conventions->flatMap(fn (Convention $c) => $c->rubriques->flatMap(fn ($r) => $r->demandesDepenses->map(fn (DemandeDepense $d) => [
            'objet' => $d->objet,
            'montant' => $d->montant,
            'rubrique' => $r->libelle,
            'convention' => $c->bailleur->sigle ?? $c->bailleur->nom,
            'date_paiement' => $d->paiement?->date_paiement?->toDateString(),
        ])
        )
        )->values();

        $paiementsDirects = $projet->conventions->flatMap(fn (Convention $c) => $c->paiementsDirects->map(fn ($p) => [
            'objet' => $p->objet_depense,
            'montant' => $p->montant,
            'rubrique' => $p->rubrique?->libelle,
            'convention' => $c->bailleur->sigle ?? $c->bailleur->nom,
            'date_paiement' => $p->date_paiement?->toDateString(),
        ])
        )->values();

        $budgetPrevu = $projet->conventions->sum('montant_fcfa');
        $totalVersements = $conventions->sum('total_versements');
        $totalDepenses = $conventions->sum('total_depenses') + $conventions->sum('total_paiements_directs');

        $ecartTemps = null;
        $ecartTempsLabel = null;

        if ($projet->date_fin_prevue) {
            $dateRef = $projet->date_fin_reelle ?? now();
            $ecartTemps = (int) $projet->date_fin_prevue->diffInDays($dateRef, false);
            $ecartTempsLabel = $ecartTemps > 0
                ? "{$ecartTemps} jour(s) de retard"
                : ($ecartTemps < 0 ? abs($ecartTemps).' jour(s) d\'avance' : 'Dans les délais');
        }

        return [
            'projet' => [
                'id' => $projet->id,
                'titre' => $projet->titre,
                'porteur' => $projet->porteur->name,
                'status' => $projet->status->value,
                'status_label' => $projet->status->label(),
                'date_debut' => $projet->date_debut?->toDateString(),
                'date_fin_prevue' => $projet->date_fin_prevue?->toDateString(),
                'date_fin_reelle' => $projet->date_fin_reelle?->toDateString(),
            ],
            'conventions' => $conventions->all(),
            'demandes' => $demandes->all(),
            'paiements_directs' => $paiementsDirects->all(),
            'analyse_ecarts' => [
                'budget_prevu' => $budgetPrevu,
                'total_versements' => $totalVersements,
                'total_depenses' => $totalDepenses,
                'ecart_budget' => $budgetPrevu - $totalDepenses,
                'taux_execution' => $budgetPrevu > 0 ? round(($totalDepenses / $budgetPrevu) * 100, 1) : 0,
                'ecart_temps_jours' => $ecartTemps,
                'ecart_temps_label' => $ecartTempsLabel,
            ],
        ];
    }
}
