<?php

namespace App\Models;

use App\Enums\ModePaiement;
use Database\Factories\PaiementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_demande', 'paiement_montant', 'paiement_date', 'paiement_mode', 'paiement_reference', 'id_enregistreur_paiement'])]
class Paiement extends Model
{
    /** @use HasFactory<PaiementFactory> */
    use HasFactory;

    protected $primaryKey = 'id_paiement';

    protected function casts(): array
    {
        return [
            'paiement_montant' => 'integer',
            'paiement_date' => 'date',
            'paiement_mode' => ModePaiement::class,
        ];
    }

    /** Transparent id accessor so $paiement->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDepense::class, 'id_demande');
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_enregistreur_paiement');
    }
}
