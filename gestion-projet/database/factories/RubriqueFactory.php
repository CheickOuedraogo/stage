<?php

namespace Database\Factories;

use App\Models\Convention;
use App\Models\Rubrique;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rubrique>
 */
class RubriqueFactory extends Factory
{
    protected $model = Rubrique::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_convention' => Convention::factory(),
            'rubrique_libelle' => fake()->words(3, true),
            'rubrique_montant_prevu' => fake()->numberBetween(1000000, 20000000),
            'rubrique_description' => fake()->sentence(),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
