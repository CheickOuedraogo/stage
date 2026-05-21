<?php

namespace App\Models;

use Database\Factories\RubriqueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_convention', 'rubrique_libelle', 'rubrique_montant_prevu', 'rubrique_description'])]
class Rubrique extends Model
{
    /** @use HasFactory<RubriqueFactory> */
    use HasFactory;

    protected $table = 'rubriques';

    protected $primaryKey = 'id_rubrique';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'rubrique_montant_prevu' => 'integer',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }

    public function demandesDepenses(): HasMany
    {
        return $this->hasMany(DemandeDepense::class, 'id_rubrique');
    }

    public function paiementsDirects(): HasMany
    {
        return $this->hasMany(PaiementDirect::class, 'id_rubrique');
    }
}
