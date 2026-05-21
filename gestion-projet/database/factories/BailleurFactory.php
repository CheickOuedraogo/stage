<?php

namespace Database\Factories;

use App\Models\Bailleur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bailleur>
 */
class BailleurFactory extends Factory
{
    protected $model = Bailleur::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bailleur_nom' => fake()->company(),
            'bailleur_sigle' => strtoupper(fake()->lexify('???')),
            'bailleur_type' => fake()->randomElement(['multilatéral', 'bilatéral', 'fondation', 'ONG']),
            'bailleur_pays' => fake()->country(),
            'bailleur_contact' => fake()->name(),
            'bailleur_email' => fake()->companyEmail(),
            'bailleur_telephone' => fake()->phoneNumber(),
            'bailleur_adresse' => fake()->address(),
            'bailleur_description' => fake()->paragraph(),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
