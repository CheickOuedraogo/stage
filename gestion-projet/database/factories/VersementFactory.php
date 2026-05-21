<?php

namespace Database\Factories;

use App\Models\Convention;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Versement>
 */
class VersementFactory extends Factory
{
    protected $model = Versement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_convention' => Convention::factory(),
            'versement_montant' => fake()->numberBetween(1000000, 50000000),
            'versement_date_reception' => fake()->dateTimeBetween('-6 months', 'now'),
            'versement_description' => fake()->sentence(),
            'versement_reference' => strtoupper(fake()->bothify('V-####-????')),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
