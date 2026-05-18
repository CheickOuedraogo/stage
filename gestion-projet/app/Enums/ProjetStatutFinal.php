<?php

namespace App\Enums;

enum ProjetStatutFinal: string
{
    case Succes = 'succes';
    case Echec = 'echec';

    public function label(): string
    {
        return match ($this) {
            self::Succes => 'Succès',
            self::Echec => 'Échec',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Succes => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::Echec => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        };
    }
}
