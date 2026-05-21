<?php

namespace App\Models;

use App\Enums\StatutFinalProjet;
use App\Enums\StatutProjet;
use Database\Factories\ProjetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['id_porteur', 'projet_titre', 'projet_description', 'projet_objectifs', 'projet_activites', 'projet_montant_estime', 'projet_statut', 'statut_final', 'projet_date_debut', 'projet_date_fin_prevue', 'projet_date_fin_reelle'])]
class Projet extends Model
{
    /** @use HasFactory<ProjetFactory> */
    use HasFactory;

    protected $table = 'projets';

    protected $primaryKey = 'id_projet';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'projet_statut' => StatutProjet::class,
            'statut_final' => StatutFinalProjet::class,
            'projet_montant_estime' => 'integer',
            'projet_date_debut' => 'date',
            'projet_date_fin_prevue' => 'date',
            'projet_date_fin_reelle' => 'date',
        ];
    }

    public function porteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_porteur');
    }

    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class, 'id_projet');
    }

    public function versements(): HasManyThrough
    {
        return $this->hasManyThrough(Versement::class, Convention::class, 'id_projet', 'id_convention');
    }

    public function scopePourPorteur(Builder $query, int $idPorteur): void
    {
        $query->where('id_porteur', $idPorteur);
    }

    /** Montant total des versements reçus pour ce projet */
    public function getMontantTotalVersementsAttribute(): int
    {
        return $this->versements()->sum('versement_montant');
    }

    /** Pourcentage de financement mobilisé */
    public function getPourcentageFinancementAttribute(): int
    {
        if ($this->projet_montant_estime === 0) {
            return 0;
        }

        $totalVersements = $this->versements()->sum('versement_montant');

        return (int) min(100, round(($totalVersements / $this->projet_montant_estime) * 100));
    }
}
