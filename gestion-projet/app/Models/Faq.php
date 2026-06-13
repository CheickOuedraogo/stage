<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['faq_question', 'faq_reponse', 'role_key'])]
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    protected $table = 'faq';

    protected $primaryKey = 'id_faq';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    protected function casts(): array
    {
        return [
            'role_key' => RoleUtilisateur::class,
        ];
    }

    public function scopePourRole(Builder $query, RoleUtilisateur $role): void
    {
        $query->where('role_key', $role->value)->orderBy('id_faq');
    }

    public function scopeActif(Builder $query): void
    {
        $query->whereNotNull('role_key')->orderBy('id_faq');
    }

    public function getEstActifAttribute(): bool
    {
        return $this->role_key !== null;
    }
}
