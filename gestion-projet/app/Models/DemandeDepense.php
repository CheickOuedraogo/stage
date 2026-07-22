<?php

namespace App\Models;

use App\Enums\StatutDemande;
use Database\Factories\DemandeDepenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'id_rubrique', 'id_convention', 'id_porteur', 'demande_montant', 'demande_objet', 'demande_description',
    'demande_justificatif', 'demande_statut', 'demande_motif_rejet', 'demande_rapport',
    'demande_rapport_motif_rejet',
    'demande_date_validation_daf', 'id_validateur_daf', 'demande_date_validation_ac', 'id_validateur_ac',
    'demande_rapport_valide_daf',
])]
class DemandeDepense extends Model
{
    /** @use HasFactory<DemandeDepenseFactory> */
    use HasFactory;

    protected $table = 'demandes_depense';

    protected $primaryKey = 'id_demande';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'demande_statut' => StatutDemande::class,
            'demande_montant' => 'integer',
            'demande_date_validation_daf' => 'datetime',
            'demande_date_validation_ac' => 'datetime',
            'demande_rapport_valide_daf' => 'boolean',
        ];
    }

    public function rubrique(): BelongsTo
    {
        return $this->belongsTo(Rubrique::class, 'id_rubrique');
    }

    public function convention(): BelongsTo
    {
        return $this->belongsTo(Convention::class, 'id_convention');
    }

    public function porteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_porteur');
    }

    public function validateurDaf(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_validateur_daf');
    }

    public function validateurAgentComptable(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_validateur_ac');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'id_demande');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $this->scopeActif($query);
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->whereNotIn('demande_statut', [
            StatutDemande::RejeteeDaf->value,
            StatutDemande::RejeteeAgentComptable->value,
            StatutDemande::Terminee->value,
        ]);
    }

    public function scopeEnAttenteDaf(Builder $query): Builder
    {
        return $query->whereIn('demande_statut', [
            StatutDemande::Soumise->value,
            StatutDemande::RapportSoumis->value,
        ]);
    }

    public function scopeEnAttenteAgentComptable(Builder $query): Builder
    {
        return $query->where('demande_statut', StatutDemande::ValideeDaf);
    }

    public function getPossedeJustificatifAttribute(): bool
    {
        return (bool) $this->demande_justificatif
            && Storage::disk('private')->exists($this->demande_justificatif);
    }

    public function getPossedeRapportAttribute(): bool
    {
        return (bool) $this->demande_rapport
            && Storage::disk('private')->exists($this->demande_rapport);
    }
}
