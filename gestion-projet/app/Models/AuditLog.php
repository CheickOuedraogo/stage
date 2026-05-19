<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

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
    ];

    protected function casts(): array
    {
        return [
            'audit_anciennes_valeurs' => 'array',
            'audit_nouvelles_valeurs' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** Transparent id accessor so $auditLog->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur');
    }

    /**
     * Create an audit log entry.
     *
     * @param  array<string, mixed>  $data
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
        ]);
    }
}
