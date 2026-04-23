<?php

namespace App\Models;

use Database\Factories\PaiementDirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['convention_id', 'rubrique_id', 'montant', 'objet_depense', 'description', 'date_paiement', 'enregistre_par'])]
class PaiementDirect extends Model
{
    /** @use HasFactory<PaiementDirectFactory> */
    use HasFactory;

    protected $table = 'paiements_directs';

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'date_paiement' => 'date',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class);
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
