<?php

namespace App\Models;

use Database\Factories\BailleurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'sigle', 'type', 'pays', 'contact', 'email', 'telephone', 'adresse', 'description'])]
class Bailleur extends Model
{
    /** @use HasFactory<BailleurFactory> */
    use HasFactory;

    public function conventions(): HasMany
    {
        return $this->hasMany(Convention::class);
    }
}
