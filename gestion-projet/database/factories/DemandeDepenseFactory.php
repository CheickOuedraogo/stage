<?php

namespace Database\Factories;

use App\Enums\StatutDemande;
use App\Models\DemandeDepense;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemandeDepense>
 */
class DemandeDepenseFactory extends Factory
{
    protected $model = DemandeDepense::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rubrique' => Rubrique::factory(),
            'id_convention' => fn (array $attributes) => Rubrique::find($attributes['id_rubrique'])->id_convention,
            'id_porteur' => Utilisateur::factory()->porteur(),
            'demande_montant' => fake()->numberBetween(100000, 5000000),
            'demande_objet' => fake()->sentence(4),
            'demande_description' => fake()->paragraph(),
            'demande_justificatif' => null,
            'demande_statut' => StatutDemande::Soumise,
            'cree_le' => now(),
            'mis_a_jour_le' => now(),
        ];
    }

    public function soumise(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::Soumise,
        ]);
    }

    public function valideeDaf(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::ValideeDaf,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => Utilisateur::factory()->daf()->create()->id_utilisateur,
        ]);
    }

    public function valideeAc(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::ValideeAgentComptable,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => Utilisateur::factory()->daf()->create()->id_utilisateur,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => Utilisateur::factory()->ac()->create()->id_utilisateur,
        ]);
    }

    public function valideAgentComptable(): static
    {
        return $this->valideeAc();
    }

    public function payee(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::Payee,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => Utilisateur::factory()->daf()->create()->id_utilisateur,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => Utilisateur::factory()->ac()->create()->id_utilisateur,
        ]);
    }

    public function rapportSoumis(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::RapportSoumis,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => Utilisateur::factory()->daf()->create()->id_utilisateur,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => Utilisateur::factory()->ac()->create()->id_utilisateur,
            'demande_rapport' => 'rapports/test-rapport.pdf',
            'demande_rapport_valide_daf' => false,
        ]);
    }

    public function terminee(): static
    {
        return $this->state(fn (array $attributes) => [
            'demande_statut' => StatutDemande::Terminee,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => Utilisateur::factory()->daf()->create()->id_utilisateur,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => Utilisateur::factory()->ac()->create()->id_utilisateur,
            'demande_rapport' => 'rapports/test-rapport.pdf',
            'demande_rapport_valide_daf' => true,
        ]);
    }
}
