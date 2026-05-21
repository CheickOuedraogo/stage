<?php

namespace Database\Factories;

use App\Enums\StatutProjet;
use App\Models\Projet;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Projet>
 */
class ProjetFactory extends Factory
{
    protected $model = Projet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_porteur' => Utilisateur::factory()->porteur(),
            'projet_titre' => fake()->sentence(5),
            'projet_description' => fake()->paragraph(),
            'projet_objectifs' => fake()->paragraph(),
            'projet_activites' => fake()->paragraph(),
            'projet_montant_estime' => fake()->numberBetween(10000000, 500000000),
            'projet_statut' => StatutProjet::EnAttenteFinancement,
            'projet_date_debut' => fake()->dateTimeBetween('-1 year', 'now'),
            'projet_date_fin_prevue' => fake()->dateTimeBetween('now', '+2 years'),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
