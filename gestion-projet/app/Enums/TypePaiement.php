<?php

namespace App\Enums;

enum TypePaiement: string
{
    case Direct = 'direct';
    case Normal = 'normal';
    case Indirect = 'indirect';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Direct',
            self::Normal => 'Normal',
            self::Indirect => 'Indirect',
        };
    }
}
