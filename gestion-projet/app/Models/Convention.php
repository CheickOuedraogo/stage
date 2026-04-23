<?php

namespace App\Models;

use App\Enums\ConventionForme;
use App\Enums\ConventionStatus;
use Database\Factories\ConventionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['projet_id', 'bailleur_id', 'titre', 'description', 'montant', 'forme', 'devise_origine', 'taux_conversion', 'montant_fcfa', 'status', 'date_signature', 'date_debut', 'date_fin'])]
class Convention extends Model
{
    /** @use HasFactory<ConventionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'forme' => ConventionForme::class,
            'status' => ConventionStatus::class,
            'montant' => 'integer',
            'montant_fcfa' => 'integer',
            'taux_conversion' => 'decimal:6',
            'date_signature' => 'date',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function bailleur(): BelongsTo
    {
        return $this->belongsTo(Bailleur::class);
    }

    public function rubriques(): HasMany
    {
        return $this->hasMany(Rubrique::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    public function demandesDepenses(): HasMany
    {
        return $this->hasMany(DemandeDepense::class);
    }

    public function paiementsDirects(): HasMany
    {
        return $this->hasMany(PaiementDirect::class);
    }

    /** Somme des rubriques budgétaires */
    public function getTotalRubriquesAttribute(): int
    {
        return $this->rubriques()->sum('montant_prevu');
    }

    /** Somme des versements reçus */
    public function getTotalVersementsAttribute(): int
    {
        return $this->versements()->sum('montant');
    }
}
