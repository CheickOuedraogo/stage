<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Projet>
 */
class ProjetFactory extends Factory
{
    public function definition(): array
    {
        $debut = $this->faker->dateTimeBetween('-3 years', '-6 months');
        $finPrevue = $this->faker->dateTimeBetween($debut, '+2 years');

        return [
            'id_porteur' => User::factory(),
            'projet_titre' => $this->faker->sentence(4),
            'projet_description' => $this->faker->paragraphs(3, true),
            'projet_objectifs' => $this->faker->paragraphs(2, true),
            'projet_activites' => $this->faker->paragraphs(2, true),
            'projet_montant_estime' => $this->faker->numberBetween(50_000_000, 500_000_000),
            'projet_statut' => $this->faker->randomElement(ProjectStatus::cases())->value,
            'projet_date_debut' => $debut,
            'projet_date_fin_prevue' => $finPrevue,
            'projet_date_fin_reelle' => null,
        ];
    }

    public function enCours(): static
    {
        return $this->state(['projet_statut' => ProjectStatus::EnCours->value]);
    }

    public function termine(): static
    {
        return $this->state(fn (array $attrs) => [
            'projet_statut' => ProjectStatus::Termine->value,
            'projet_date_fin_reelle' => $this->faker->dateTimeBetween($attrs['projet_date_debut'], 'now'),
        ]);
    }

    public function enAttenteFinancement(): static
    {
        return $this->state(['projet_statut' => ProjectStatus::EnAttenteFinancement->value]);
    }
}
