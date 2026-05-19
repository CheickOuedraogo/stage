<?php

namespace App\Models;

use App\Enums\VersementType;
use Database\Factories\VersementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_convention', 'versement_montant', 'versement_date_reception', 'versement_type', 'versement_description', 'versement_reference'])]
class Versement extends Model
{
    /** @use HasFactory<VersementFactory> */
    use HasFactory;

    protected $primaryKey = 'id_versement';

    protected function casts(): array
    {
        return [
            'versement_type' => VersementType::class,
            'versement_montant' => 'integer',
            'versement_date_reception' => 'date',
        ];
    }

    /** Transparent id accessor so $versement->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }
}
