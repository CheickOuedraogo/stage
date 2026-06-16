<?php

namespace App\Policies;

use App\Enums\StatutProjet;
use App\Models\Projet;
use App\Models\Utilisateur;

class ProjetPolicy
{
    public function cloturer(Utilisateur $user, Projet $projet): bool
    {
        return $user->estDaf() || $user->estAgentComptable();
    }

    public function mettreEnCours(Utilisateur $user, Projet $projet): bool
    {
        return ($user->estDaf() || $user->estAgentComptable()) && $projet->projet_statut === StatutProjet::EnAttenteFinancement;
    }

    public function voirBilan(Utilisateur $user, Projet $projet): bool
    {
        if ($user->estDaf() || $user->estAgentComptable()) {
            return true;
        }

        return $user->estPorteur() && $projet->id_porteur === $user->id_utilisateur;
    }
}
