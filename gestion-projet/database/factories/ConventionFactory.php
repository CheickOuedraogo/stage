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
            'id_projet' => Projet::factory(),
            'id_bailleur' => Bailleur::factory(),
            'convention_titre' => 'Convention de financement — '.$this->faker->sentence(3),
            'convention_description' => $this->faker->paragraphs(2, true),
            'convention_montant' => $montant,
            'convention_forme' => $this->faker->randomElement(ConventionForme::cases())->value,
            'convention_devise' => $devise,
            'convention_taux_conversion' => $taux,
            'convention_montant_fcfa' => $montantFcfa,
            'convention_statut' => ConventionStatus::Active->value,
            'convention_date_signature' => $signature,
            'convention_date_debut' => $debut,
            'convention_date_fin' => $fin,
        ];
    }
}
