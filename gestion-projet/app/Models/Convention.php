<?php

namespace App\Models;

use App\Enums\FormeConvention;
use App\Enums\StatutConvention;
use Database\Factories\ConventionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['id_projet', 'id_bailleur', 'convention_titre', 'convention_description', 'convention_montant', 'convention_forme', 'convention_devise', 'convention_taux_conversion', 'convention_statut', 'convention_date_signature', 'convention_date_debut', 'convention_date_fin'])]
class Convention extends Model
{
    /** @use HasFactory<ConventionFactory> */
    use HasFactory;

    protected $table = 'conventions';

    protected $primaryKey = 'id_convention';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'convention_forme' => FormeConvention::class,
            'convention_statut' => StatutConvention::class,
            'convention_montant' => 'integer',
            'convention_taux_conversion' => 'decimal:6',
            'convention_date_signature' => 'date',
            'convention_date_debut' => 'date',
            'convention_date_fin' => 'date',
        ];
    }

    /** Montant converti en FCFA (montant × taux_conversion) */
    public function getMontantFcfaAttribute(): int
    {
        return (int) round($this->convention_montant * (float) $this->convention_taux_conversion);
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'id_projet');
    }

    public function bailleur(): BelongsTo
    {
        return $this->belongsTo(Bailleur::class, 'id_bailleur');
    }

    public function rubriques(): HasMany
    {
        return $this->hasMany(Rubrique::class, 'id_convention');
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class, 'id_convention');
    }

    public function demandesDepenses(): HasMany
    {
        return $this->hasMany(DemandeDepense::class, 'id_convention');
    }

    public function paiementsDirects(): HasMany
    {
        return $this->hasMany(PaiementDirect::class, 'id_convention');
    }

    /** Somme des rubriques budgétaires */
    public function getMontantTotalRubriquesAttribute(): int
    {
        return $this->rubriques()->sum('rubrique_montant_prevu');
    }

    /** Somme des versements reçus */
    public function getMontantTotalVersementsAttribute(): int
    {
        return $this->versements()->sum('versement_montant');
    }
}
