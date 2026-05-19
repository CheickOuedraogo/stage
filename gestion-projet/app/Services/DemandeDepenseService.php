<?php

namespace App\Services;

use App\Enums\DemandeStatus;
use App\Enums\UserRole;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Rubrique;
use App\Models\User;
use App\Notifications\DemandeStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeDepenseService
{
    /**
     * Vérifie qu'aucune demande active n'existe déjà sur la convention.
     */
    public function assertPasDeDemandeActive(Convention $convention, ?int $excludeId = null): void
    {
        $query = DemandeDepense::where('id_convention', $convention->id)->active();

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
                DemandeStatus::Soumise->value,
                DemandeStatus::ValidéeDaf->value,
                DemandeStatus::ValidéeAc->value,
                DemandeStatus::Payee->value,
                DemandeStatus::RapportSoumis->value,
                DemandeStatus::Terminee->value,
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
    public function validerDaf(DemandeDepense $demande, User $daf): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::Soumise, 403);
        abort_unless($daf->utilisateur_role === UserRole::Daf, 403);

        $demande->update([
            'demande_statut' => DemandeStatus::ValidéeDaf,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => $daf->id,
            'demande_motif_rejet' => null,
        ]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::ValidéeDaf));

        $this->notifyAc($demande, DemandeStatus::ValidéeDaf);
    }

    /**
     * DAF rejette une demande.
     */
    public function rejeterDaf(DemandeDepense $demande, User $daf, string $motif): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::Soumise, 403);
        abort_unless($daf->utilisateur_role === UserRole::Daf, 403);

        $demande->update([
            'demande_statut' => DemandeStatus::RejetéeDaf,
            'demande_motif_rejet' => $motif,
        ]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::RejetéeDaf, $motif));
    }

    /**
     * AC valide une demande.
     */
    public function validerAc(DemandeDepense $demande, User $ac): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::ValidéeDaf, 403);
        abort_unless($ac->utilisateur_role === UserRole::Ac, 403);

        $demande->update([
            'demande_statut' => DemandeStatus::ValidéeAc,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => $ac->id,
            'demande_motif_rejet' => null,
        ]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::ValidéeAc));
        $this->notifyDaf($demande, DemandeStatus::ValidéeAc);
    }

    /**
     * AC rejette une demande.
     */
    public function rejeterAc(DemandeDepense $demande, User $ac, string $motif): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::ValidéeDaf, 403);
        abort_unless($ac->utilisateur_role === UserRole::Ac, 403);

        $demande->update([
            'demande_statut' => DemandeStatus::RejetéeAc,
            'demande_motif_rejet' => $motif,
        ]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::RejetéeAc, $motif));
        $this->notifyDaf($demande, DemandeStatus::RejetéeAc);
    }

    /**
     * AC enregistre le paiement après validation AC.
     */
    public function enregistrerPaiement(DemandeDepense $demande, User $ac, array $data): Paiement
    {
        abort_unless($demande->demande_statut === DemandeStatus::ValidéeAc, 403);
        abort_unless($ac->utilisateur_role === UserRole::Ac, 403);

        $paiement = Paiement::create([
            'id_demande' => $demande->id,
            'paiement_montant' => $data['montant'],
            'paiement_date' => $data['date_paiement'],
            'paiement_mode' => $data['mode_paiement'],
            'paiement_reference' => $data['reference'] ?? null,
            'id_enregistreur_paiement' => $ac->id,
        ]);

        $demande->update(['demande_statut' => DemandeStatus::Payee]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::Payee));

        return $paiement;
    }

    /**
     * Porteur uploade le rapport d'exécution après paiement.
     */
    public function soumettreRapport(DemandeDepense $demande, User $porteur, string $rapportPath): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::Payee, 403);
        abort_unless($demande->id_porteur === $porteur->id, 403);

        $demande->update([
            'demande_statut' => DemandeStatus::RapportSoumis,
            'demande_rapport' => $rapportPath,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);

        $this->notifyDafAc($demande, DemandeStatus::RapportSoumis);
    }

    /**
     * DAF valide le rapport — la demande est terminée si AC a aussi validé.
     */
    public function validerRapportDaf(DemandeDepense $demande, User $daf): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::RapportSoumis, 403);
        abort_unless($daf->utilisateur_role === UserRole::Daf, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_daf' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => DemandeStatus::Terminee]);
                $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::Terminee));
            }
        });
    }

    /**
     * AC valide le rapport — la demande est terminée si DAF a aussi validé.
     */
    public function validerRapportAc(DemandeDepense $demande, User $ac): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::RapportSoumis, 403);
        abort_unless($ac->utilisateur_role === UserRole::Ac, 403);

        DB::transaction(function () use ($demande) {
            $demande->newQuery()->where('id_demande', $demande->id)->lockForUpdate()->sole();
            $demande->update(['demande_rapport_valide_ac' => true]);
            $demande->refresh();

            if ($demande->demande_rapport_valide_daf && $demande->demande_rapport_valide_ac) {
                $demande->update(['demande_statut' => DemandeStatus::Terminee]);
                $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::Terminee));
            }
        });
    }

    /**
     * Rejette le rapport uploadé — le porteur doit re-soumettre.
     */
    public function rejeterRapport(DemandeDepense $demande, User $user): void
    {
        abort_unless($demande->demande_statut === DemandeStatus::RapportSoumis, 403);
        abort_unless(
            in_array($user->utilisateur_role, [UserRole::Daf, UserRole::Ac]),
            403
        );

        $demande->update([
            'demande_statut' => DemandeStatus::Payee,
            'demande_rapport' => null,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);

        $demande->porteur->notify(new DemandeStatusChanged($demande, DemandeStatus::Payee, 'Rapport rejeté, veuillez soumettre un nouveau rapport.'));
    }

    private function notifyAc(DemandeDepense $demande, DemandeStatus $status): void
    {
        User::where('utilisateur_role', UserRole::Ac->value)
            ->get()
            ->each(fn (User $u) => $u->notify(new DemandeStatusChanged($demande, $status)));
    }

    private function notifyDaf(DemandeDepense $demande, DemandeStatus $status): void
    {
        User::where('utilisateur_role', UserRole::Daf->value)
            ->get()
            ->each(fn (User $u) => $u->notify(new DemandeStatusChanged($demande, $status)));
    }

    private function notifyDafAc(DemandeDepense $demande, DemandeStatus $status): void
    {
        User::whereIn('utilisateur_role', [UserRole::Daf->value, UserRole::Ac->value])
            ->get()
            ->each(fn (User $u) => $u->notify(new DemandeStatusChanged($demande, $status)));
    }
}
