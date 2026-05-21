<?php

namespace App\Services;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDemande;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use App\Notifications\StatutDemandeChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeDepenseService
{
    /**
     * Vérifie qu'aucune demande active n'existe déjà sur la convention.
     */
    public function assertPasDeDemandeActive(Convention $convention, ?int $excludeId = null): void
    {
        $query = DemandeDepense::where('id_convention', $convention->id_utilisateur)->actif();

        if ($excludeId) {
            $query->where('id_demande', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'convention_id' => 'Une demande est déjà en cours pour cette convention.',
            ]);
        }
    }

    /**
     * Vérifie que le montant demandé ne dépasse pas le solde de la rubrique.
     */
    public function assertBudgetSuffisant(Rubrique $rubrique, int $montant, ?int $excludeDemande = null): void
    {
        $consomme = $rubrique->demandesDepenses()
            ->whereIn('demande_statut', [
                StatutDemande::Soumise->value,
                StatutDemande::ValidéeDaf->value,
                StatutDemande::ValidéeAgentComptable->value,
                StatutDemande::Payee->value,
                StatutDemande::RapportSoumis->value,
                StatutDemande::Terminee->value,
            ])
            ->when($excludeDemande, fn ($q) => $q->where('id_demande', '!=', $excludeDemande))
            ->sum('demande_montant');

        $consommePaiementsDirects = $rubrique->paiementsDirects()->sum('paiement_direct_montant');
        $totalConsomme = $consomme + $consommePaiementsDirects;
        $solde = $rubrique->rubrique_montant_prevu - $totalConsomme;

        if ($montant > $solde) {
            throw ValidationException::withMessages([
                'montant' => "Le montant demandé ({$montant} FCFA) dépasse le solde disponible de la rubrique ({$solde} FCFA).",
            ]);
        }
    }

    /**
     * DAF valide une demande.
     */
    public function validerDaf(DemandeDepense $demande, Utilisateur $daf): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Soumise, 403);
        abort_unless($daf->utilisateur_role === RoleUtilisateur::Daf, 403);

        $demande->update([
            'demande_statut' => StatutDemande::ValidéeDaf,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => $daf->id_utilisateur,
            'demande_motif_rejet' => null,
        ]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::ValidéeDaf));

        $this->notifyAgentComptable($demande, StatutDemande::ValidéeDaf);
    }

    /**
     * DAF rejette une demande.
     */
    public function rejeterDaf(DemandeDepense $demande, Utilisateur $daf, string $motif): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Soumise, 403);
        abort_unless($daf->utilisateur_role === RoleUtilisateur::Daf, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RejetéeDaf,
            'demande_motif_rejet' => $motif,
        ]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::RejetéeDaf, $motif));
    }

    /**
     * AC valide une demande.
     */
    public function validerAgentComptable(DemandeDepense $demande, Utilisateur $ac): void
    {
        abort_unless($demande->demande_statut === StatutDemande::ValidéeDaf, 403);
        abort_unless($ac->utilisateur_role === RoleUtilisateur::AgentComptable, 403);

        $demande->update([
            'demande_statut' => StatutDemande::ValidéeAgentComptable,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => $ac->id_utilisateur,
            'demande_motif_rejet' => null,
        ]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::ValidéeAgentComptable));
        $this->notifyDaf($demande, StatutDemande::ValidéeAgentComptable);
    }

    /**
     * AC rejette une demande.
     */
    public function rejeterAgentComptable(DemandeDepense $demande, Utilisateur $ac, string $motif): void
    {
        abort_unless($demande->demande_statut === StatutDemande::ValidéeDaf, 403);
        abort_unless($ac->utilisateur_role === RoleUtilisateur::AgentComptable, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RejetéeAgentComptable,
            'demande_motif_rejet' => $motif,
        ]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::RejetéeAgentComptable, $motif));
        $this->notifyDaf($demande, StatutDemande::RejetéeAgentComptable);
    }

    /**
     * AC enregistre le paiement après validation AC.
     */
    public function enregistrerPaiement(DemandeDepense $demande, Utilisateur $ac, array $data): Paiement
    {
        abort_unless($demande->demande_statut === StatutDemande::ValidéeAgentComptable, 403);
        abort_unless($ac->utilisateur_role === RoleUtilisateur::AgentComptable, 403);

        $paiement = Paiement::create([
            'id_demande' => $demande->id_utilisateur,
            'paiement_montant' => $data['montant'],
            'paiement_date' => $data['date_paiement'],
            'paiement_mode' => $data['mode_paiement'],
            'paiement_reference' => $data['reference'] ?? null,
            'id_enregistreur_paiement' => $ac->id_utilisateur,
        ]);

        $demande->update(['demande_statut' => StatutDemande::Payee]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::Payee));

        return $paiement;
    }

    /**
     * Porteur uploade le rapport d'exécution après paiement.
     */
    public function soumettreRapport(DemandeDepense $demande, Utilisateur $porteur, string $rapportPath): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Payee, 403);
        abort_unless($demande->id_utilisateur_porteur === $porteur->id_utilisateur, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RapportSoumis,
            'demande_rapport' => $rapportPath,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);

        $this->notifyDafAgentComptable($demande, StatutDemande::RapportSoumis);
    }

    /**
     * DAF valide le rapport — la demande est terminée si AC a aussi validé.
     */
    public function validerRapportDaf(DemandeDepense $demande, Utilisateur $daf): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless($daf->utilisateur_role === RoleUtilisateur::Daf, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id_utilisateur)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_daf' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => StatutDemande::Terminee]);
                $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::Terminee));
            }
        });
    }

    /**
     * AC valide le rapport — la demande est terminée si DAF a aussi validé.
     */
    public function validerRapportAgentComptable(DemandeDepense $demande, Utilisateur $ac): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless($ac->utilisateur_role === RoleUtilisateur::AgentComptable, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id_utilisateur)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_ac' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => StatutDemande::Terminee]);
                $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::Terminee));
            }
        });
    }

    /**
     * Rejette le rapport uploadé — le porteur doit re-soumettre.
     */
    public function rejeterRapport(DemandeDepense $demande, Utilisateur $user): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless(
            in_array($user->utilisateur_role, [RoleUtilisateur::Daf, RoleUtilisateur::AgentComptable]),
            403
        );

        $demande->update([
            'demande_statut' => StatutDemande::Payee,
            'demande_rapport' => null,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);

        $demande->porteur->notify(new StatutDemandeChange($demande, StatutDemande::Payee, 'Rapport rejeté, veuillez soumettre un nouveau rapport.'));
    }

    private function notifyAgentComptable(DemandeDepense $demande, StatutDemande $status): void
    {
        Utilisateur::where('utilisateur_role', RoleUtilisateur::AgentComptable->value)
            ->get()
            ->each(fn (Utilisateur $u) => $u->notify(new StatutDemandeChange($demande, $status)));
    }

    private function notifyDaf(DemandeDepense $demande, StatutDemande $status): void
    {
        Utilisateur::where('utilisateur_role', RoleUtilisateur::Daf->value)
            ->get()
            ->each(fn (Utilisateur $u) => $u->notify(new StatutDemandeChange($demande, $status)));
    }

    private function notifyDafAgentComptable(DemandeDepense $demande, StatutDemande $status): void
    {
        Utilisateur::whereIn('utilisateur_role', [RoleUtilisateur::Daf->value, RoleUtilisateur::AgentComptable->value])
            ->get()
            ->each(fn (Utilisateur $u) => $u->notify(new StatutDemandeChange($demande, $status)));
    }
}
