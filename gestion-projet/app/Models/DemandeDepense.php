<?php

namespace App\Models;

use App\Enums\DemandeStatus;
use Database\Factories\DemandeDepenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'rubrique_id', 'convention_id', 'porteur_id', 'montant', 'objet', 'description',
    'justificatif_path', 'status', 'motif_rejet', 'rapport_path',
    'validee_daf_at', 'validee_daf_par', 'validee_ac_at', 'validee_ac_par',
    'rapport_validee_daf', 'rapport_validee_ac',
])]
class DemandeDepense extends Model
{
    /** @use HasFactory<DemandeDepenseFactory> */
    use HasFactory;

    protected $table = 'demandes_depenses';

    protected function casts(): array
    {
        return [
            'status' => DemandeStatus::class,
            'montant' => 'integer',
            'validee_daf_at' => 'datetime',
            'validee_ac_at' => 'datetime',
            'rapport_validee_daf' => 'boolean',
            'rapport_validee_ac' => 'boolean',
        ];
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class);
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class);
    }

    public function porteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'porteur_id');
    }

    public function validateurDaf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_daf_par');
    }

    public function validateurAc(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_ac_par');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'demande_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            DemandeStatus::RejetéeDaf->value,
            DemandeStatus::RejetéeAc->value,
            DemandeStatus::Terminee->value,
        ]);
    }

    public function scopeEnAttenteDaf(Builder $query): Builder
    {
        return $query->where('status', DemandeStatus::Soumise);
    }

    public function scopeEnAttenteAc(Builder $query): Builder
    {
        return $query->where('status', DemandeStatus::ValidéeDaf);
    }
}
