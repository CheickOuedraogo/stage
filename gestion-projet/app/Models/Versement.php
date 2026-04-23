<?php

namespace App\Models;

use App\Enums\VersementType;
use Database\Factories\VersementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['convention_id', 'montant', 'date_reception', 'type', 'description', 'reference'])]
class Versement extends Model
{
    /** @use HasFactory<VersementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => VersementType::class,
            'montant' => 'integer',
            'date_reception' => 'date',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class);
    }
}
