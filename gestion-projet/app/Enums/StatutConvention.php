<?php

namespace App\Enums;

enum StatutConvention: string
{
    case Active = 'active';
    case Suspendue = 'suspendue';
    case Terminee = 'terminee';
    case Annulee = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspendue => 'Suspendue',
            self::Terminee => 'Terminée',
            self::Annulee => 'Annulée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::Suspendue => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            self::Terminee => 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
            self::Annulee => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        };
    }
}
