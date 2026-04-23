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
            'porteur_id' => User::factory(),
            'titre' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'objectifs' => $this->faker->paragraphs(2, true),
            'activites' => $this->faker->paragraphs(2, true),
            'montant_estime' => $this->faker->numberBetween(50_000_000, 500_000_000),
            'status' => $this->faker->randomElement(ProjectStatus::cases())->value,
            'date_debut' => $debut,
            'date_fin_prevue' => $finPrevue,
            'date_fin_reelle' => null,
        ];
    }

    public function enCours(): static
    {
        return $this->state(['status' => ProjectStatus::EnCours->value]);
    }

    public function termine(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => ProjectStatus::Termine->value,
            'date_fin_reelle' => $this->faker->dateTimeBetween($attrs['date_debut'], 'now'),
        ]);
    }

    public function enAttenteFinancement(): static
    {
        return $this->state(['status' => ProjectStatus::EnAttenteFinancement->value]);
    }
}
