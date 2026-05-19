<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'avatar_path' => null,
            'telephone' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(['utilisateur_role' => UserRole::Admin]);
    }

    public function daf(): static
    {
        return $this->state(['utilisateur_role' => UserRole::Daf]);
    }

    public function ac(): static
    {
        return $this->state(['utilisateur_role' => UserRole::Ac]);
    }

    public function porteur(): static
    {
        return $this->state(['utilisateur_role' => UserRole::Porteur]);
    }

    public function inactive(): static
    {
        return $this->state(['utilisateur_actif' => false]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
