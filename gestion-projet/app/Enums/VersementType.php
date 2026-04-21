<?php

namespace App\Enums;

enum VersementType: string
{
    case Avance = 'avance';
    case Tranche = 'tranche';

    public function label(): string
    {
        return match ($this) {
            self::Avance => 'Avance',
            self::Tranche => 'Tranche',
        };
    }
}
