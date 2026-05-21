<?php

namespace App\Enums;

enum TypeNotification: string
{
    case DemandeStatutChange = 'demande_statut_change';
    case ProjetCloture = 'projet_cloture';

    public function label(): string
    {
        return match ($this) {
            self::DemandeStatutChange => 'Changement de statut de demande',
            self::ProjetCloture => 'Clôture de projet',
        };
    }
}
