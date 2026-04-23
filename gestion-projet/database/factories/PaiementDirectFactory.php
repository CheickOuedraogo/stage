<?php

namespace Database\Factories;

use App\Models\Convention;
use App\Models\PaiementDirect;
use App\Models\Rubrique;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaiementDirect>
 */
class PaiementDirectFactory extends Factory
{
    public function definition(): array
    {
        $convention = Convention::factory()->create();

        return [
            'convention_id' => $convention->id,
            'rubrique_id' => Rubrique::factory()->for($convention),
            'montant' => $this->faker->numberBetween(500_000, 10_000_000),
            'objet_depense' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'date_paiement' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'enregistre_par' => User::factory()->porteur(),
        ];
    }
}
