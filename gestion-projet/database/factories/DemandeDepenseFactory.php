<?php

namespace Database\Factories;

use App\Enums\DemandeStatus;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Rubrique;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemandeDepense>
 */
class DemandeDepenseFactory extends Factory
{
    public function definition(): array
    {
        $convention = Convention::factory()->create();
        $rubrique = Rubrique::factory()->for($convention)->create();

        return [
            'id_rubrique' => $rubrique->id,
            'id_convention' => $convention->id,
            'id_porteur' => User::factory()->porteur(),
            'demande_montant' => $this->faker->numberBetween(500_000, 5_000_000),
            'demande_objet' => $this->faker->sentence(5),
            'demande_description' => $this->faker->paragraph(),
            'demande_justificatif' => null,
            'demande_statut' => DemandeStatus::Soumise,
            'demande_motif_rejet' => null,
            'demande_rapport' => null,
            'demande_date_validation_daf' => null,
            'id_validateur_daf' => null,
            'demande_date_validation_ac' => null,
            'id_validateur_ac' => null,
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ];
    }

    public function soumise(): static
    {
        return $this->state(['demande_statut' => DemandeStatus::Soumise]);
    }

    public function valideeDaf(): static
    {
        return $this->state(fn () => [
            'demande_statut' => DemandeStatus::ValidéeDaf,
            'demande_date_validation_daf' => now(),
            'id_validateur_daf' => User::factory()->daf()->create()->id,
        ]);
    }

    public function valideAc(): static
    {
        return $this->state(fn () => [
            'demande_statut' => DemandeStatus::ValidéeAc,
            'demande_date_validation_daf' => now()->subDay(),
            'id_validateur_daf' => User::factory()->daf()->create()->id,
            'demande_date_validation_ac' => now(),
            'id_validateur_ac' => User::factory()->ac()->create()->id,
        ]);
    }

    public function payee(): static
    {
        return $this->state(fn () => [
            'demande_statut' => DemandeStatus::Payee,
            'demande_date_validation_daf' => now()->subDays(3),
            'id_validateur_daf' => User::factory()->daf()->create()->id,
            'demande_date_validation_ac' => now()->subDays(2),
            'id_validateur_ac' => User::factory()->ac()->create()->id,
        ]);
    }

    public function rapportSoumis(): static
    {
        return $this->payee()->state([
            'demande_statut' => DemandeStatus::RapportSoumis,
            'demande_rapport' => 'rapports/test.pdf',
            'demande_rapport_valide_daf' => false,
            'demande_rapport_valide_ac' => false,
        ]);
    }

    public function rejetee(): static
    {
        return $this->state([
            'demande_statut' => DemandeStatus::RejetéeDaf,
            'demande_motif_rejet' => 'Justificatif insuffisant.',
        ]);
    }

    public function terminee(): static
    {
        return $this->rapportSoumis()->state([
            'demande_statut' => DemandeStatus::Terminee,
            'demande_rapport_valide_daf' => true,
            'demande_rapport_valide_ac' => true,
        ]);
    }
}
