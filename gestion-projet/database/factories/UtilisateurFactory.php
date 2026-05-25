<?php

namespace Database\Factories;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'utilisateur_nom' => fake()->name(),
            'utilisateur_email' => fake()->unique()->safeEmail(),
            'email_verifie_le' => now(),
            'utilisateur_mot_de_passe' => static::$password ??= Hash::make('utilisateur_mot_de_passe'),
            'jeton_souvenir' => Str::random(10),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_avatar_chemin' => null,
            'utilisateur_telephone' => null,
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role_key' => RoleUtilisateur::Administrateur]);
    }

    public function daf(): static
    {
        return $this->state(['role_key' => RoleUtilisateur::Daf]);
    }

    public function ac(): static
    {
        return $this->state(['role_key' => RoleUtilisateur::AgentComptable]);
    }

    public function porteur(): static
    {
        return $this->state(['role_key' => RoleUtilisateur::Porteur]);
    }

    public function inactif(): static
    {
        return $this->state(['utilisateur_actif' => false]);
    }

    public function inactive(): static
    {
        return $this->inactif();
    }

    public function non_verifie(): static
    {
        return $this->state(['email_verifie_le' => null]);
    }
}
