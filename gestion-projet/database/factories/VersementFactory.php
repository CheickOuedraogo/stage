<?php

namespace Database\Factories;

use App\Enums\VersementType;
use App\Models\Convention;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Versement>
 */
class VersementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'convention_id' => Convention::factory(),
            'montant' => $this->faker->numberBetween(5_000_000, 50_000_000),
            'date_reception' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'type' => $this->faker->randomElement(VersementType::cases())->value,
            'description' => $this->faker->optional()->sentence(),
            'reference' => strtoupper($this->faker->lexify('VRS-????-####')),
        ];
    }
}
