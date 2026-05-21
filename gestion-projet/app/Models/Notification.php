<?php

namespace App\Models;

use App\Enums\TypeNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_utilisateur',
    'type_notification',
    'id_demande',
    'id_projet',
    'notification_objet',
    'notification_libelle_statut',
    'notification_motif',
    'lu_le',
])]
class Notification extends Model
{
    public $timestamps = false;

    protected $table = 'notifications';

    protected $primaryKey = 'id_notification';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type_notification' => TypeNotification::class,
            'lu_le' => 'datetime',
            'cree_le' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(DemandeDepense::class, 'id_demande');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'id_projet');
    }

    public function scopeNonLue(Builder $query): Builder
    {
        return $query->whereNull('lu_le');
    }

    public function marquerCommentLue(): void
    {
        if (! $this->lu_le) {
            $this->update(['lu_le' => now()]);
        }
    }

    /** Créer une notification pour un changement de statut de demande */
    public static function pourDemandeStatut(
        int $idUtilisateur,
        DemandeDepense $demande,
        string $libelleStatut,
        ?string $motif = null,
    ): self {
        return self::create([
            'id_utilisateur' => $idUtilisateur,
            'type_notification' => TypeNotification::DemandeStatutChange,
            'id_demande' => $demande->id_demande,
            'notification_objet' => $demande->demande_objet,
            'notification_libelle_statut' => $libelleStatut,
            'notification_motif' => $motif,
        ]);
    }

    /** Créer une notification de clôture de projet */
    public static function pourProjetCloture(int $idUtilisateur, Projet $projet): self
    {
        return self::create([
            'id_utilisateur' => $idUtilisateur,
            'type_notification' => TypeNotification::ProjetCloture,
            'id_projet' => $projet->id_projet,
            'notification_objet' => $projet->projet_titre,
        ]);
    }
}
