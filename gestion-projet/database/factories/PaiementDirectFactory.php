<?php

namespace Database\Factories;

use App\Models\Convention;
use App\Models\PaiementDirect;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaiementDirect>
 */
class PaiementDirectFactory extends Factory
{
    protected $model = PaiementDirect::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_convention' => Convention::factory(),
            'id_rubrique' => fn (array $attributes) => Rubrique::factory(['id_convention' => $attributes['id_convention']]),
            'paiement_direct_montant' => fake()->numberBetween(100000, 5000000),
            'paiement_direct_objet' => fake()->sentence(4),
            'paiement_direct_description' => fake()->paragraph(),
            'paiement_direct_date' => now(),
            'id_enregistreur_paiement_direct' => Utilisateur::factory()->porteur(),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
