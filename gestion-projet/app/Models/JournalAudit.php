<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalAudit extends Model
{
    public $timestamps = false;

    protected $table = 'journaux_audit';

    protected $primaryKey = 'id_audit';

    protected $fillable = [
        'id_utilisateur',
        'audit_action',
        'audit_entite_type',
        'audit_entite_id',
        'audit_anciennes_valeurs',
        'audit_nouvelles_valeurs',
        'audit_adresse_ip',
        'audit_navigateur',
        'audit_description',
        'cree_le',
    ];

    protected function casts(): array
    {
        return [
            'audit_anciennes_valeurs' => 'array',
            'audit_nouvelles_valeurs' => 'array',
            'cree_le' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur');
    }

    /**
     * Créer une entrée dans le journal d'audit.
     */
    public static function log(
        string $action,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
    ): self {
        return self::create([
            'id_utilisateur' => auth()->id(),
            'audit_action' => $action,
            'audit_entite_type' => $auditable ? get_class($auditable) : null,
            'audit_entite_id' => $auditable?->getKey(),
            'audit_anciennes_valeurs' => $oldValues,
            'audit_nouvelles_valeurs' => $newValues,
            'audit_adresse_ip' => request()->ip(),
            'audit_navigateur' => request()->userAgent(),
            'audit_description' => $description,
            'cree_le' => now(),
        ]);
    }
}
