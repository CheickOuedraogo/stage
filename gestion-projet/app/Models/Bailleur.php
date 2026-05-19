<?php

namespace App\Models;

use Database\Factories\BailleurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bailleur_nom', 'bailleur_sigle', 'bailleur_type', 'bailleur_pays', 'contact', 'email', 'telephone', 'adresse', 'description'])]
class Bailleur extends Model
{
    /** @use HasFactory<BailleurFactory> */
    use HasFactory;

    protected $primaryKey = 'id_bailleur';

    /** Transparent id accessor so $bailleur->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class, 'id_bailleur');
    }
}
