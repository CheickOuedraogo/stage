<?php

namespace App\Models;

use Database\Factories\BailleurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bailleur_nom', 'bailleur_sigle', 'bailleur_type', 'bailleur_pays', 'bailleur_contact', 'bailleur_email', 'bailleur_telephone', 'bailleur_adresse', 'bailleur_description'])]
class Bailleur extends Model
{
    /** @use HasFactory<BailleurFactory> */
    use HasFactory;

    protected $table = 'bailleurs';

    protected $primaryKey = 'id_bailleur';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class, 'id_bailleur');
    }
}
