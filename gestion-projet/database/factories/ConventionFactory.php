<?php

namespace Database\Factories;

use App\Enums\ConventionForme;
use App\Enums\ConventionStatus;
use App\Models\Bailleur;
use App\Models\Convention;
use App\Models\Projet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Convention>
 */
class ConventionFactory extends Factory
{
    /** @var array<string, float> Taux statiques vers FCFA */
    private array $tauxVersXof = [
        'XOF' => 1.0,
        'EUR' => 655.957,
        'USD' => 605.0,
    ];

    public function definition(): array
    {
        $devise = $this->faker->randomElement(['XOF', 'EUR', 'USD']);
        $taux = $this->tauxVersXof[$devise];
        $montant = $this->faker->numberBetween(10_000_000, 200_000_000);
        $montantFcfa = (int) round($montant * $taux);
        $signature = $this->faker->dateTimeBetween('-3 years', '-1 year');
        $debut = $this->faker->dateTimeBetween($signature, '-6 months');
        $fin = $this->faker->dateTimeBetween($debut, '+2 years');

        return [
            'projet_id' => Projet::factory(),
            'bailleur_id' => Bailleur::factory(),
            'titre' => 'Convention de financement — '.$this->faker->sentence(3),
            'description' => $this->faker->paragraphs(2, true),
            'montant' => $montant,
            'forme' => $this->faker->randomElement(ConventionForme::cases())->value,
            'devise_origine' => $devise,
            'taux_conversion' => $taux,
            'montant_fcfa' => $montantFcfa,
            'status' => ConventionStatus::Active->value,
            'date_signature' => $signature,
            'date_debut' => $debut,
            'date_fin' => $fin,
        ];
    }
}
