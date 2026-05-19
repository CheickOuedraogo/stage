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
            'id_convention' => $convention->id,
            'id_rubrique' => Rubrique::factory()->for($convention),
            'paiement_direct_montant' => $this->faker->numberBetween(500_000, 10_000_000),
            'paiement_direct_objet' => $this->faker->sentence(4),
            'paiement_direct_description' => $this->faker->optional()->paragraph(),
            'paiement_direct_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'id_enregistreur_paiement_direct' => User::factory()->porteur(),
        ];
    }
}
