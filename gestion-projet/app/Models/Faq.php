<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['faq_question', 'faq_reponse', 'faq_actif', 'visible_porteur', 'visible_daf', 'visible_ac'])]
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
            'faq_actif' => 'boolean',
            'visible_porteur' => 'boolean',
            'visible_daf' => 'boolean',
            'visible_ac' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('faq_actif', true)->orderBy('id_faq');
    }

    public function isVisibleFor(RoleUtilisateur $role): bool
    {
        return match ($role) {
            RoleUtilisateur::Porteur => $this->visible_porteur,
            RoleUtilisateur::Daf => $this->visible_daf,
            RoleUtilisateur::AgentComptable => $this->visible_ac,
            default => false,
        };
    }
}
