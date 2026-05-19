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
            'id_demande' => DemandeDepense::factory()->valideAc(),
            'paiement_montant' => $this->faker->numberBetween(500_000, 5_000_000),
            'paiement_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'paiement_mode' => $this->faker->randomElement(ModePaiement::cases())->value,
            'paiement_reference' => $this->faker->optional(0.7)->numerify('REF-####'),
            'id_enregistreur_paiement' => User::factory()->ac(),
        ];
    }
}
