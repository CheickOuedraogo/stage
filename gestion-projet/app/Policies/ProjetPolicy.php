<?php

namespace App\Policies;

use App\Models\Projet;
use App\Models\User;

class ProjetPolicy
{
    public function cloturer(User $user, Projet $projet): bool
    {
        return $user->isDaf();
    }

    public function voirBilan(User $user, Projet $projet): bool
    {
        if ($user->isDaf() || $user->isAc()) {
            return true;
        }

        return $user->isPorteur() && $projet->porteur_id === $user->id;
    }
}
