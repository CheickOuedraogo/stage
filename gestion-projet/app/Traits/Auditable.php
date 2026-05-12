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
                description: self::buildDescription('created', $model),
            );
        });

        static::updated(function ($model) {
            $dirty = self::getAuditableAttributes($model, 'dirty');
            if (empty($dirty)) {
                return;
            }

            AuditLog::log(
                action: 'updated',
                auditable: $model,
                oldValues: self::getAuditableAttributes($model, 'original'),
                newValues: $dirty,
                description: self::buildDescription('updated', $model, $dirty),
            );
        });

        static::deleted(function ($model) {
            AuditLog::log(
                action: 'deleted',
                auditable: $model,
                oldValues: self::getAuditableAttributes($model),
                description: self::buildDescription('deleted', $model),
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
        $excluded = ['password', 'remember_token', 'updated_at', 'created_at'];

        $attributes = match ($type) {
            'original' => $model->getOriginal(),
            'dirty' => $model->getDirty(),
            default => $model->getAttributes(),
        };

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Build a human-readable description for the audit log entry.
     *
     * @param  array<string, mixed>  $changed
     */
    private static function buildDescription(string $event, mixed $model, array $changed = []): string
    {
        $className = class_basename($model);
        $identifier = self::getModelIdentifier($model);

        return match ($event) {
            'created' => "Création de {$className}{$identifier}",
            'deleted' => "Suppression de {$className}{$identifier}",
            'updated' => self::buildUpdateDescription($className, $identifier, $changed),
            default => "{$event} {$className}{$identifier}",
        };
    }

    private static function getModelIdentifier(mixed $model): string
    {
        foreach (['nom', 'titre', 'objet', 'libelle', 'question', 'name'] as $field) {
            if (! empty($model->{$field})) {
                $value = mb_strimwidth((string) $model->{$field}, 0, 60, '…');

                return " « {$value} »";
            }
        }

        return ' #'.$model->getKey();
    }

    /**
     * @param  array<string, mixed>  $changed
     */
    private static function buildUpdateDescription(string $className, string $identifier, array $changed): string
    {
        $fieldLabels = [
            'name' => 'nom',
            'email' => 'e-mail',
            'role' => 'rôle',
            'is_active' => 'statut',
            'telephone' => 'téléphone',
            'status' => 'statut',
            'montant' => 'montant',
            'montant_prevu' => 'montant prévu',
            'montant_fcfa' => 'montant FCFA',
            'date_fin_reelle' => 'date de clôture',
            'date_reception' => 'date de réception',
            'motif_rejet' => 'motif de rejet',
            'titre' => 'titre',
            'description' => 'description',
            'active' => 'maintenance',
            'reponse' => 'réponse',
            'question' => 'question',
        ];

        $fields = array_map(
            fn ($key) => $fieldLabels[$key] ?? $key,
            array_keys($changed)
        );

        if (count($fields) === 0) {
            return "Modification de {$className}{$identifier}";
        }

        $fieldList = implode(', ', array_slice($fields, 0, 4));
        $more = count($fields) > 4 ? ' (+'.count($fields) - 4 .' autre(s))' : '';

        return "Modification de {$className}{$identifier} — champ(s) : {$fieldList}{$more}";
    }
}
