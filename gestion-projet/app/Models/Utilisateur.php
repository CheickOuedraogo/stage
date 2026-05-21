<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use App\Traits\Auditable;
use Database\Factories\UtilisateurFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @method static Builder actif()
 * @method static Builder parRole(RoleUtilisateur $role)
 */
#[Fillable(['utilisateur_nom', 'utilisateur_email', 'utilisateur_mot_de_passe', 'utilisateur_role', 'utilisateur_actif', 'utilisateur_avatar_chemin', 'utilisateur_telephone'])]
#[Hidden(['utilisateur_mot_de_passe', 'jeton_souvenir'])]
class Utilisateur extends Authenticatable
{
    /** @use HasFactory<UtilisateurFactory> */
    use Auditable, HasFactory;

    protected $table = 'utilisateurs';

    protected $primaryKey = 'id_utilisateur';

    const CREATED_AT = 'cree_le';

    const UPDATED_AT = 'mis_a_jour_le';

    /**
     * Récupérer les attributs à caster.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verifie_le' => 'datetime',
            'utilisateur_mot_de_passe' => 'hashed',
            'utilisateur_role' => RoleUtilisateur::class,
            'utilisateur_actif' => 'boolean',
        ];
    }

    /** Scope: uniquement les utilisateurs actifs */
    public function scopeAgentComptabletif(Builder $query): void
    {
        $query->where('utilisateur_actif', true);
    }

    /** Scope: filtrer par rôle */
    public function scopeParRole(Builder $query, RoleUtilisateur $role): void
    {
        $query->where('utilisateur_role', $role->value);
    }

    /** Vérifier si l'utilisateur a un rôle donné */
    public function aRole(RoleUtilisateur $role): bool
    {
        return $this->utilisateur_role === $role;
    }

    public function estAdministrateur(): bool
    {
        return $this->aRole(RoleUtilisateur::Administrateur);
    }

    public function estDaf(): bool
    {
        return $this->aRole(RoleUtilisateur::Daf);
    }

    public function estAgentComptable(): bool
    {
        return $this->aRole(RoleUtilisateur::AgentComptable);
    }

    public function estPorteur(): bool
    {
        return $this->aRole(RoleUtilisateur::Porteur);
    }

    /** Route nommée pour le tableau de bord de cet utilisateur. */
    public function routeTableauBord(): string
    {
        return match ($this->utilisateur_role) {
            RoleUtilisateur::Administrateur => route('admin.dashboard'),
            RoleUtilisateur::Daf => route('daf.dashboard'),
            RoleUtilisateur::AgentComptable => route('ac.dashboard'),
            RoleUtilisateur::Porteur => route('porteur.dashboard'),
        };
    }

    /** Journaux d'audit créés par cet utilisateur */
    public function journauxAudit(): HasMany
    {
        return $this->hasMany(JournalAudit::class, 'id_utilisateur');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'id_utilisateur')->orderByDesc('cree_le');
    }

    public function notificationsNonLues(): HasMany
    {
        return $this->hasMany(Notification::class, 'id_utilisateur')->whereNull('lu_le');
    }

    /** @var list<string> */
    protected $appends = ['label_role', 'url_avatar'];

    /** Libellé court pour le rôle */
    public function getLabelRoleAttribute(): ?string
    {
        return $this->utilisateur_role?->shortLabel();
    }

    /** URL de l'avatar */
    public function getUrlAvatarAttribute(): ?string
    {
        return $this->utilisateur_avatar_chemin
            ? asset("storage/{$this->utilisateur_avatar_chemin}")
            : null;
    }

    /** Pour Laravel Auth : nom de la colonne mot de passe */
    public function getAuthPasswordName(): string
    {
        return 'utilisateur_mot_de_passe';
    }

    /** Pour Laravel Auth : nom de la colonne jeton souvenir */
    public function getRememberTokenName(): string
    {
        return 'jeton_souvenir';
    }
}
