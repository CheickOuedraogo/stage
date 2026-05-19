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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $libelles = [
            'Matériel informatique et équipements',
            'Missions et déplacements',
            'Prestations intellectuelles',
            'Fonctionnement et fournitures',
            'Formation et renforcement de capacités',
            'Acquisition de données et publications',
            'Communication et dissémination',
            'Imprévus',
        ];

        return [
            'id_convention' => Convention::factory(),
            'rubrique_libelle' => $this->faker->randomElement($libelles),
            'rubrique_montant_prevu' => $this->faker->numberBetween(2_000_000, 40_000_000),
            'rubrique_description' => $this->faker->sentence(),
        ];
    }
}
