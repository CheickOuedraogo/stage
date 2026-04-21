<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::log(
                action: 'created',
                auditable: $model,
                newValues: self::getAuditableAttributes($model),
            );
        });

        static::updated(function ($model) {
            AuditLog::log(
                action: 'updated',
                auditable: $model,
                oldValues: self::getAuditableAttributes($model, 'original'),
                newValues: self::getAuditableAttributes($model, 'dirty'),
            );
        });

        static::deleted(function ($model) {
            AuditLog::log(
                action: 'deleted',
                auditable: $model,
                oldValues: self::getAuditableAttributes($model),
            );
        });
    }

    /**
     * Get auditable attributes, excluding sensitive fields.
     *
     * @return array<string, mixed>
     */
    private static function getAuditableAttributes(mixed $model, string $type = 'current'): array
    {
        $excluded = ['password', 'remember_token'];

        $attributes = match ($type) {
            'original' => $model->getOriginal(),
            'dirty' => $model->getDirty(),
            default => $model->getAttributes(),
        };

        return array_diff_key($attributes, array_flip($excluded));
    }
}
