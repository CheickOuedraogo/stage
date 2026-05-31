<?php

namespace App\Services;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDemande;
use App\Enums\TypePaiement;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeDepenseService
{
    public function assertConventionARubriques(Convention $convention): void
    {
        if ($convention->rubriques()->count() === 0) {
            throw ValidationException::withMessages([
                'convention_id' => 'Cette convention ne contient aucune rubrique budgétaire. Ajoutez-en avant de soumettre une demande.',
            ]);
        }
    }

    public function assertPasDeDemandeActive(Convention $convention, ?int $excludeId = null): void
    {
        $query = DemandeDepense::where('id_convention', $convention->id_convention)->actif();

        if ($excludeId) {
            $query->where('id_demande', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'convention_id' => 'Une demande est déjà en cours pour cette convention.',
            ]);
        }
    }

    public function assertBudgetSuffisant(Rubrique $rubrique, int $montant, ?int $excludeDemande = null): void
    {
        $consomme = $rubrique->demandesDepenses()
            ->whereIn('demande_statut', [
                StatutDemande::Soumise->value,
                StatutDemande::ValideeDaf->value,
                StatutDemande::ValideeAgentComptable->value,
                StatutDemande::Payee->value,
                StatutDemande::RapportSoumis->value,
                StatutDemande::Terminee->value,
            ])
            ->when($excludeDemande, fn ($q) => $q->where('id_demande', '!=', $excludeDemande))
            ->sum('demande_montant');

        $consommePaiementsDirects = $rubrique->paiementsDirects()->sum('paiement_montant');
        $solde = $rubrique->rubrique_montant - $consomme - $consommePaiementsDirects;

        if ($montant > $solde) {
            throw ValidationException::withMessages([
                'montant' => "Le montant demandé ({$montant} FCFA) dépasse le solde disponible de la rubrique ({$solde} FCFA).",
            ]);
        }
    }

    public function validerDaf(DemandeDepense $demande, Utilisateur $daf): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Soumise, 403);
        abort_unless($daf->role_key === RoleUtilisateur::Daf, 403);

        $demande->update([
            'demande_statut' => StatutDemande::ValideeDaf,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => $daf->id_utilisateur,
            'demande_motif_rejet' => null,
        ]);

        Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::ValideeDaf->label());
        $this->notifyAgentComptable($demande, StatutDemande::ValideeDaf);
    }

    public function rejeterDaf(DemandeDepense $demande, Utilisateur $daf, string $motif): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Soumise, 403);
        abort_unless($daf->role_key === RoleUtilisateur::Daf, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RejeteeDaf,
            'demande_motif_rejet' => $motif,
        ]);

        Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::RejeteeDaf->label(), $motif);
    }

    public function validerAgentComptable(DemandeDepense $demande, Utilisateur $ac): void
    {
        abort_unless($demande->demande_statut === StatutDemande::ValideeDaf, 403);
        abort_unless($ac->role_key === RoleUtilisateur::AgentComptable, 403);

        $demande->update([
            'demande_statut' => StatutDemande::ValideeAgentComptable,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => $ac->id_utilisateur,
            'demande_motif_rejet' => null,
        ]);

        Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::ValideeAgentComptable->label());
        $this->notifyDaf($demande, StatutDemande::ValideeAgentComptable);
    }

    public function rejeterAgentComptable(DemandeDepense $demande, Utilisateur $ac, string $motif): void
    {
        abort_unless($demande->demande_statut === StatutDemande::ValideeDaf, 403);
        abort_unless($ac->role_key === RoleUtilisateur::AgentComptable, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RejeteeAgentComptable,
            'demande_motif_rejet' => $motif,
        ]);

        Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::RejeteeAgentComptable->label(), $motif);
        $this->notifyDaf($demande, StatutDemande::RejeteeAgentComptable);
    }

    public function enregistrerPaiement(DemandeDepense $demande, Utilisateur $ac, array $data): Paiement
    {
        abort_unless($demande->demande_statut === StatutDemande::ValideeAgentComptable, 403);
        abort_unless($ac->role_key === RoleUtilisateur::AgentComptable, 403);

        $paiement = Paiement::create([
            'id_demande' => $demande->id_demande,
            'paiement_montant' => $demande->demande_montant,
            'paiement_date' => $data['date_paiement'],
            'paiement_mode' => $data['mode_paiement'],
            'paiement_reference' => $data['reference'] ?? null,
            'id_enregistreur_paiement' => $ac->id_utilisateur,
            'id_convention' => $demande->id_convention,
            'id_rubrique' => $demande->id_rubrique,
            'id_projet' => $demande->convention->id_projet,
            'type_paiement' => TypePaiement::Indirect,
        ]);

        $demande->update(['demande_statut' => StatutDemande::Payee]);

        Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::Payee->label());

        return $paiement;
    }

    public function soumettreRapport(DemandeDepense $demande, Utilisateur $porteur, string $rapportPath): void
    {
        abort_unless($demande->demande_statut === StatutDemande::Payee, 403);
        abort_unless($demande->id_porteur === $porteur->id_utilisateur, 403);

        $demande->update([
            'demande_statut' => StatutDemande::RapportSoumis,
            'demande_rapport' => $rapportPath,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);

        $this->notifyDafAgentComptable($demande, StatutDemande::RapportSoumis);
    }

    public function validerRapportDaf(DemandeDepense $demande, Utilisateur $daf): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless($daf->role_key === RoleUtilisateur::Daf, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id_demande)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_daf' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => StatutDemande::Terminee]);
                Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::Terminee->label());
            }
        });
    }

    public function validerRapportAgentComptable(DemandeDepense $demande, Utilisateur $ac): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless($ac->role_key === RoleUtilisateur::AgentComptable, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id_demande)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_ac' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => StatutDemande::Terminee]);
                Notification::pourDemandeStatut($demande->id_porteur, $demande, StatutDemande::Terminee->label());
            }
        });
    }

    public function rejeterRapport(DemandeDepense $demande, Utilisateur $user, ?string $motif = null): void
    {
        abort_unless($demande->demande_statut === StatutDemande::RapportSoumis, 403);
        abort_unless(
            in_array($user->role_key, [RoleUtilisateur::Daf, RoleUtilisateur::AgentComptable]),
            403
        );

        $demande->update([
            'demande_statut' => StatutDemande::Payee,
            'demande_rapport' => null,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
            'demande_rapport_motif_rejet' => $motif,
        ]);

        $message = $motif
            ? "Rapport rejeté : {$motif}"
            : 'Rapport rejeté, veuillez soumettre un nouveau rapport.';

        Notification::pourDemandeStatut(
            $demande->id_porteur,
            $demande,
            StatutDemande::Payee->label(),
            $message,
        );
    }

    private function notifyAgentComptable(DemandeDepense $demande, StatutDemande $statut): void
    {
        Utilisateur::parRole(RoleUtilisateur::AgentComptable)->get()
            ->each(fn (Utilisateur $u) => Notification::pourDemandeStatut($u->id_utilisateur, $demande, $statut->label()));
    }

    private function notifyDaf(DemandeDepense $demande, StatutDemande $statut): void
    {
        Utilisateur::parRole(RoleUtilisateur::Daf)->get()
            ->each(fn (Utilisateur $u) => Notification::pourDemandeStatut($u->id_utilisateur, $demande, $statut->label()));
    }

    private function notifyDafAgentComptable(DemandeDepense $demande, StatutDemande $statut): void
    {
        Utilisateur::whereIn('role_key', [RoleUtilisateur::Daf->value, RoleUtilisateur::AgentComptable->value])
            ->get()
            ->each(fn (Utilisateur $u) => Notification::pourDemandeStatut($u->id_utilisateur, $demande, $statut->label()));
    }
}
