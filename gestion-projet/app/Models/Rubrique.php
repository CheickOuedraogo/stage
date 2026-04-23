<?php

namespace App\Models;

use Database\Factories\RubriqueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['convention_id', 'libelle', 'montant_prevu', 'description'])]
class Rubrique extends Model
{
    /** @use HasFactory<RubriqueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'montant_prevu' => 'integer',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class);
    }

    public function demandesDepenses(): HasMany
    {
        return $this->hasMany(DemandeDepense::class);
    }

    public function paiementsDirects(): HasMany
    {
        return $this->hasMany(PaiementDirect::class);
    }
}
