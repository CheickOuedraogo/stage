<?php

namespace App\Models;

use App\Enums\TypeVersement;
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

    protected $table = 'versements';

    protected $primaryKey = 'id_versement';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'versement_type' => TypeVersement::class,
            'versement_montant' => 'integer',
            'versement_date_reception' => 'date',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }
}
