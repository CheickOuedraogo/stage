<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $table = 'parametres';

    protected $fillable = ['parametre_cle', 'parametre_valeur'];

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    /**
     * Récupérer une valeur de paramètre par clé.
     */
    public static function get(string $cle, mixed $defaut = null): mixed
    {
        $parametre = static::where('parametre_cle', $cle)->first();

        return $parametre?->parametre_valeur ?? $defaut;
    }

    /**
     * Définir (ou mettre à jour) une valeur de paramètre par clé.
     */
    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(
            ['parametre_cle' => $cle],
            ['parametre_valeur' => $valeur],
        );
    }

    /**
     * Vérifier si le mode maintenance est actif.
     */
    public static function estMaintenanceActive(): bool
    {
        return static::get('maintenance_mode', 'false') === 'true';
    }

    /**
     * Récupérer la date de fin de maintenance (null si non définie).
     */
    public static function getMaintenanceJusqua(): ?Carbon
    {
        $valeur = static::get('maintenance_until');

        return $valeur ? Carbon::parse($valeur) : null;
    }
}
