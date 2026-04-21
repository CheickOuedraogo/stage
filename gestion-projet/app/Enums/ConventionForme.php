<?php

namespace App\Enums;

enum ConventionForme: string
{
    case Pret = 'pret';
    case Don = 'don';

    public function label(): string
    {
        return match ($this) {
            self::Pret => 'Prêt',
            self::Don => 'Don',
        };
    }
}
