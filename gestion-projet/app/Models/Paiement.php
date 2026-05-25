<?php

namespace App\Models;

use App\Enums\ModePaiement;
use App\Enums\TypePaiement;
use Database\Factories\PaiementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_demande', 'paiement_montant', 'paiement_date', 'paiement_mode',
    'paiement_reference', 'id_enregistreur_paiement',
    'type_paiement', 'id_convention', 'id_rubrique',
    'paiement_objet', 'paiement_description', 'id_projet',
])]
class Paiement extends Model
{
    /** @use HasFactory<PaiementFactory> */
    use HasFactory;

    protected $table = 'paiements';

    protected $primaryKey = 'id_paiement';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'paiement_montant' => 'integer',
            'paiement_date' => 'date',
            'paiement_mode' => ModePaiement::class,
            'type_paiement' => TypePaiement::class,
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDepense::class, 'id_demande');
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class, 'id_rubrique');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'id_projet');
    }

    public function enregistreur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_enregistreur_paiement');
    }
}
