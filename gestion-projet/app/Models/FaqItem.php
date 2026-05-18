<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'reponse', 'ordre', 'is_active', 'roles_cibles'])]
class FaqItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ordre' => 'integer',
            'roles_cibles' => 'array',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('ordre');
    }

    public function isVisibleFor(UserRole $role): bool
    {
        $targets = $this->roles_cibles ?? ['all'];

        return in_array('all', $targets) || in_array($role->value, $targets);
    }
}
