<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['faq_question', 'faq_reponse', 'faq_actif', 'roles_cibles'])]
class FaqItem extends Model
{
    protected $primaryKey = 'id_faq';

    protected function casts(): array
    {
        return [
            'faq_actif' => 'boolean',
            'roles_cibles' => 'array',
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
        $targets = $this->roles_cibles ?? ['all'];

        return in_array('all', $targets) || in_array($role->value, $targets);
    }
}
