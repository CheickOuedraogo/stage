<?php

namespace App\Policies;

use App\Models\Utilisateur;

class UtilisateurPolicy
{
    public function viewAny(Utilisateur $currentUtilisateur): bool
    {
        return $currentUtilisateur->estAdministrateur();
    }

    public function view(Utilisateur $currentUtilisateur, Utilisateur $user): bool
    {
        return $currentUtilisateur->estAdministrateur();
    }

    public function create(Utilisateur $currentUtilisateur): bool
    {
        return $currentUtilisateur->estAdministrateur();
    }

    public function update(Utilisateur $currentUtilisateur, Utilisateur $user): bool
    {
        return $currentUtilisateur->estAdministrateur();
    }

    public function delete(Utilisateur $currentUtilisateur, Utilisateur $user): bool
    {
        // Administrateur cannot delete themselves
        return $currentUtilisateur->estAdministrateur() && $currentUtilisateur->id_utilisateur !== $user->id_utilisateur;
    }
}
