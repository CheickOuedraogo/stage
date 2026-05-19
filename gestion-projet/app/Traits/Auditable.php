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
        foreach (['bailleur_nom', 'projet_titre', 'convention_titre', 'rubrique_libelle', 'faq_question', 'demande_objet', 'name'] as $field) {
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
            'utilisateur_role' => 'rôle',
            'utilisateur_actif' => 'statut',
            'telephone' => 'téléphone',
            'demande_statut' => 'statut',
            'convention_statut' => 'statut',
            'projet_statut' => 'statut',
            'versement_montant' => 'montant',
            'paiement_montant' => 'montant',
            'paiement_direct_montant' => 'montant',
            'demande_montant' => 'montant',
            'convention_montant' => 'montant',
            'rubrique_montant_prevu' => 'montant prévu',
            'convention_montant_fcfa' => 'montant FCFA',
            'projet_date_fin_reelle' => 'date de clôture',
            'versement_date_reception' => 'date de réception',
            'demande_motif_rejet' => 'motif de rejet',
            'projet_titre' => 'titre',
            'convention_titre' => 'titre',
            'demande_description' => 'description',
            'rubrique_description' => 'description',
            'convention_description' => 'description',
            'faq_actif' => 'maintenance',
            'faq_reponse' => 'réponse',
            'faq_question' => 'question',
            'active' => 'maintenance',
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
