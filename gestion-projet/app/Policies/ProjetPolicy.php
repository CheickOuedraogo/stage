<?php

namespace App\Policies;

use App\Models\Projet;
use App\Models\Utilisateur;

class ProjetPolicy
{
    public function cloturer(Utilisateur $user, Projet $projet): bool
    {
        return $user->estDaf();
    }

    public function voirBilan(Utilisateur $user, Projet $projet): bool
    {
        if ($user->estDaf() || $user->isAgentComptable()) {
            return true;
        }

        return $user->estPorteur() && $projet->id_utilisateur_porteur === $user->id_utilisateur;
    }
}
