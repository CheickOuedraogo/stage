<?php

namespace App\Enums;

enum ModePaiement: string
{
    case Virement = 'virement';
    case Cheque = 'cheque';
    case Especes = 'especes';

    public function label(): string
    {
        return match ($this) {
            self::Virement => 'Virement bancaire',
            self::Cheque => 'Chèque',
            self::Especes => 'Espèces',
        };
    }
}
