<?php

namespace App\Models;

use App\Enums\ModePaiement;
use Database\Factories\PaiementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['demande_id', 'montant', 'date_paiement', 'mode_paiement', 'reference', 'enregistre_par'])]
class Paiement extends Model
{
    /** @use HasFactory<PaiementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'date_paiement' => 'date',
            'mode_paiement' => ModePaiement::class,
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDepense::class, 'demande_id');
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
