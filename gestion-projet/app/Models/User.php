<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'avatar_path', 'telephone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /** Scope: only active users */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Scope: filter by role */
    public function scopeByRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role->value);
    }

    /** Check if user has a given role */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isDaf(): bool
    {
        return $this->hasRole(UserRole::Daf);
    }

    public function isAc(): bool
    {
        return $this->hasRole(UserRole::Ac);
    }

    public function isPorteur(): bool
    {
        return $this->hasRole(UserRole::Porteur);
    }

    /** Audit logs authored by this user */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /** Avatar URL — returns null if no avatar */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset('storage/'.$this->avatar_path)
            : null;
    }
}
