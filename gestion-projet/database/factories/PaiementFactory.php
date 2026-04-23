<?php

namespace Database\Factories;

use App\Enums\ModePaiement;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paiement>
 */
class PaiementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'demande_id' => DemandeDepense::factory()->valideAc(),
            'montant' => $this->faker->numberBetween(500_000, 5_000_000),
            'date_paiement' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'mode_paiement' => $this->faker->randomElement(ModePaiement::cases())->value,
            'reference' => $this->faker->optional(0.7)->numerify('REF-####'),
            'enregistre_par' => User::factory()->ac(),
        ];
    }
}
