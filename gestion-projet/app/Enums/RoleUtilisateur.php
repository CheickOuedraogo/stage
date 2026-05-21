<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case Administrateur = 'admin';
    case Daf = 'daf';
    case AgentComptable = 'ac';
    case Porteur = 'porteur';

    /** Label français pour l'affichage */
    public function label(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::Daf => 'Direction Administrateuristration et Finances',
            self::AgentComptable => 'Agent Comptable',
            self::Porteur => 'Porteur de projet',
        };
    }

    /** Label court */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::Daf => 'DAF',
            self::AgentComptable => 'AC',
            self::Porteur => 'Porteur',
        };
    }
}
