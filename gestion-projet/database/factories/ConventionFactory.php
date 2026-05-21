<?php

namespace Database\Factories;

use App\Enums\FormeConvention;
use App\Enums\StatutConvention;
use App\Models\Bailleur;
use App\Models\Convention;
use App\Models\Projet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Convention>
 */
class ConventionFactory extends Factory
{
    protected $model = Convention::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_projet' => Projet::factory(),
            'id_bailleur' => Bailleur::factory(),
            'convention_titre' => 'Convention '.fake()->word(),
            'convention_description' => fake()->paragraph(),
            'convention_montant' => fake()->numberBetween(5000000, 100000000),
            'convention_forme' => fake()->randomElement(FormeConvention::cases()),
            'convention_devise' => 'XOF',
            'convention_taux_conversion' => 1.0,
            'convention_statut' => StatutConvention::Active,
            'convention_date_signature' => fake()->dateTimeBetween('-1 month', 'now'),
            'convention_date_debut' => fake()->dateTimeBetween('now', '+1 month'),
            'convention_date_fin' => fake()->dateTimeBetween('+1 year', '+3 years'),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
