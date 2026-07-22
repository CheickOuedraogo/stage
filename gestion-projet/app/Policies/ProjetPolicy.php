<?php

namespace App\Policies;

use App\Models\Projet;
use App\Models\Utilisateur;

class ProjetPolicy
{
    public function voirBilan(Utilisateur $user, Projet $projet): bool
    {
        if ($user->estDaf() || $user->estAgentComptable()) {
            return true;
        }

        return $user->estPorteur() && $projet->id_porteur === $user->id_utilisateur;
    }
}
