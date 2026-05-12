<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'reponse', 'ordre', 'is_active'])]
class FaqItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('ordre');
    }
}
