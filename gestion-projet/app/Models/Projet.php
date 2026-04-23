<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Database\Factories\ProjetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['porteur_id', 'titre', 'description', 'objectifs', 'activites', 'montant_estime', 'status', 'date_debut', 'date_fin_prevue', 'date_fin_reelle'])]
class Projet extends Model
{
    /** @use HasFactory<ProjetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'montant_estime' => 'integer',
            'date_debut' => 'date',
            'date_fin_prevue' => 'date',
            'date_fin_reelle' => 'date',
        ];
    }

    public function porteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'porteur_id');
    }

    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class);
    }

    public function versements(): HasManyThrough
    {
        return $this->hasManyThrough(Versement::class, Convention::class);
    }

    public function scopeForPorteur(Builder $query, int $porteurId): void
    {
        $query->where('porteur_id', $porteurId);
    }

    /** Montant total des versements reçus pour ce projet */
    public function getTotalVersementsAttribute(): int
    {
        return $this->versements()->sum('montant');
    }

    /** Pourcentage de financement mobilisé */
    public function getPourcentageFinancementAttribute(): int
    {
        if ($this->montant_estime === 0) {
            return 0;
        }

        $totalVersements = $this->versements()->sum('montant');

        return (int) min(100, round(($totalVersements / $this->montant_estime) * 100));
    }
}
