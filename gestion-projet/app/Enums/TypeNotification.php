<?php

namespace App\Enums;

enum TypeNotification: string
{
    case DemandeStatutChange = 'demande_statut_change';
    case ProjetCloture = 'projet_cloture';
    case ProjetMisEnCours = 'projet_mis_en_cours';

    public function label(): string
    {
        return match ($this) {
            self::DemandeStatutChange => 'Changement de statut de demande',
            self::ProjetCloture => 'Clôture de projet',
            self::ProjetMisEnCours => 'Projet mis en cours',
        };
    }
}
