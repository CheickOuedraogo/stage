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

/**
 * @method static Builder active()
 * @method static Builder byRole(UserRole $role)
 */
#[Fillable(['name', 'email', 'password', 'utilisateur_role', 'utilisateur_actif', 'avatar_path', 'telephone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable;

    protected $primaryKey = 'id_utilisateur';

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
            'utilisateur_role' => UserRole::class,
            'utilisateur_actif' => 'boolean',
        ];
    }

    /** Scope: only active users */
    public function scopeActive(Builder $query): void
    {
        $query->where('utilisateur_actif', true);
    }

    /** Scope: filter by role */
    public function scopeByRole(Builder $query, UserRole $role): void
    {
        $query->where('utilisateur_role', $role->value);
    }

    /** Check if user has a given role */
    public function hasRole(UserRole $role): bool
    {
        return $this->utilisateur_role === $role;
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

    /** Named route for this user's dashboard — used after login/OAuth redirect. */
    public function dashboardRoute(): string
    {
        return match ($this->utilisateur_role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Daf => route('daf.dashboard'),
            UserRole::Ac => route('ac.dashboard'),
            UserRole::Porteur => route('porteur.dashboard'),
        };
    }

    /** Audit logs authored by this user */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'id_utilisateur');
    }

    /** @var list<string> */
    protected $appends = ['role_label', 'avatar_url'];

    /** Short label for the role (Admin, DAF, AC, Porteur) */
    public function getRoleLabelAttribute(): ?string
    {
        return $this->utilisateur_role?->shortLabel();
    }

    /** Avatar URL — returns null if no avatar */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? asset("storage/{$this->avatar_path}")
            : null;
    }

    /** Transparent id accessor so $user->id still works */
    public function getIdAttribute(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }
}
