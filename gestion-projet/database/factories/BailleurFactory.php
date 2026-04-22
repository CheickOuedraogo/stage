<?php

namespace Database\Factories;

use App\Models\Bailleur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bailleur>
 */
class BailleurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->company(),
            'sigle' => strtoupper($this->faker->lexify('???')),
            'type' => $this->faker->randomElement(['bilatéral', 'multilatéral']),
            'pays' => $this->faker->country(),
            'contact' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'telephone' => $this->faker->phoneNumber(),
            'adresse' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
