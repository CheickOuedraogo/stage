<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['faq_question', 'faq_reponse', 'faq_actif', 'visible_porteur', 'visible_daf', 'visible_ac'])]
class FaqItem extends Model
{
    protected $primaryKey = 'id_faq';

    protected function casts(): array
    {
        return [
            'faq_actif' => 'boolean',
            'visible_porteur' => 'boolean',
            'visible_daf' => 'boolean',
            'visible_ac' => 'boolean',
        ];
    }

    /** Transparent id accessor so $faqItem->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('faq_actif', true)->orderBy('id_faq');
    }

    public function isVisibleFor(UserRole $role): bool
    {
        return match ($role) {
            UserRole::Porteur => $this->visible_porteur,
            UserRole::Daf => $this->visible_daf,
            UserRole::Ac => $this->visible_ac,
            default => false,
        };
    }
}
