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
            'rubrique_id' => $rubrique->id,
            'convention_id' => $convention->id,
            'porteur_id' => User::factory()->porteur(),
            'montant' => $this->faker->numberBetween(500_000, 5_000_000),
            'objet' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'justificatif_path' => null,
            'status' => DemandeStatus::Soumise,
            'motif_rejet' => null,
            'rapport_path' => null,
            'validee_daf_at' => null,
            'validee_daf_par' => null,
            'validee_ac_at' => null,
            'validee_ac_par' => null,
            'rapport_validee_daf' => false,
            'rapport_validee_ac' => false,
        ];
    }

    public function soumise(): static
    {
        return $this->state(['status' => DemandeStatus::Soumise]);
    }

    public function valideeDaf(): static
    {
        return $this->state(fn () => [
            'status' => DemandeStatus::ValidéeDaf,
            'validee_daf_at' => now(),
            'validee_daf_par' => User::factory()->daf()->create()->id,
        ]);
    }

    public function valideAc(): static
    {
        return $this->state(fn () => [
            'status' => DemandeStatus::ValidéeAc,
            'validee_daf_at' => now()->subDay(),
            'validee_daf_par' => User::factory()->daf()->create()->id,
            'validee_ac_at' => now(),
            'validee_ac_par' => User::factory()->ac()->create()->id,
        ]);
    }

    public function payee(): static
    {
        return $this->state(fn () => [
            'status' => DemandeStatus::Payee,
            'validee_daf_at' => now()->subDays(3),
            'validee_daf_par' => User::factory()->daf()->create()->id,
            'validee_ac_at' => now()->subDays(2),
            'validee_ac_par' => User::factory()->ac()->create()->id,
        ]);
    }

    public function rapportSoumis(): static
    {
        return $this->payee()->state([
            'status' => DemandeStatus::RapportSoumis,
            'rapport_path' => 'rapports/test.pdf',
            'rapport_validee_daf' => false,
            'rapport_validee_ac' => false,
        ]);
    }

    public function rejetee(): static
    {
        return $this->state([
            'status' => DemandeStatus::RejetéeDaf,
            'motif_rejet' => 'Justificatif insuffisant.',
        ]);
    }

    public function terminee(): static
    {
        return $this->rapportSoumis()->state([
            'status' => DemandeStatus::Terminee,
            'rapport_validee_daf' => true,
            'rapport_validee_ac' => true,
        ]);
    }
}
