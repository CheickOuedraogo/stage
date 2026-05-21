<?php

namespace App\Models;

use Database\Factories\PaiementDirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_convention', 'id_rubrique', 'paiement_direct_montant', 'paiement_direct_objet', 'paiement_direct_description', 'paiement_direct_date', 'id_enregistreur_paiement_direct'])]
class PaiementDirect extends Model
{
    /** @use HasFactory<PaiementDirectFactory> */
    use HasFactory;

    protected $table = 'paiements_directs';

    protected $primaryKey = 'id_paiement_direct';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'paiement_direct_montant' => 'integer',
            'paiement_direct_date' => 'date',
        ];
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class, 'id_rubrique');
    }

    public function enregistreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_enregistreur_paiement_direct');
    }
}
