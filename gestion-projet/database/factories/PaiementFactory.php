<?php

namespace Database\Factories;

use App\Enums\ModePaiement;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paiement>
 */
class PaiementFactory extends Factory
{
    protected $model = Paiement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_demande' => DemandeDepense::factory()->valideeAc(),
            'paiement_montant' => fn (array $attributes) => DemandeDepense::find($attributes['id_demande'])->demande_montant,
            'paiement_date' => now(),
            'paiement_mode' => ModePaiement::Virement,
            'paiement_reference' => strtoupper(fake()->bothify('PAY-####-????')),
            'id_enregistreur_paiement' => Utilisateur::factory()->ac(),
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }
}
