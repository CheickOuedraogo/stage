<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Daf = 'daf';
    case Ac = 'ac';
    case Porteur = 'porteur';

    /** French label for display */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Daf => 'Direction Administration et Finances',
            self::Ac => 'Agent Comptable',
            self::Porteur => 'Porteur de projet',
        };
    }

    /** Short label */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Daf => 'DAF',
            self::Ac => 'AC',
            self::Porteur => 'Porteur',
        };
    }
}
