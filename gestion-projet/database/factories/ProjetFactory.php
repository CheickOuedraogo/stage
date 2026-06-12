<?php

namespace Database\Factories;

use App\Enums\StatutFinalProjet;
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
            'projet_titre' => fake()->randomElement([
                "Développement d'Énergies Renouvelables pour l'Autonomisation des Communautés Rurales au Burkina Faso",
                'Architecture et Urbanisme Durables pour des Villes Résilientes au Burkina Faso',
                'Renforcement des Capacités Institutionnelles et de la Gouvernance Locale au Burkina Faso',
                "Promotion de l'Économie Circulaire et Gestion Durable des Déchets au Burkina Faso",
                "Appui à la Digitalisation des Services Financiers pour l'Inclusion des Populations Rurales",
                'Renforcement de la Chaîne de Valeur du Mangue et des Produits Forestiers Non Ligneux',
                'Amélioration de la Résilience des Écosystèmes Aquatiques et des Ressources Halieutiques',
                "Programme d'Appui à l'Artisanat et aux Métiers Créatifs pour l'Emploi des Jeunes",
            ]),
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

    public function enCours(): static
    {
        return $this->state(['projet_statut' => StatutProjet::EnCours]);
    }

    public function termine(): static
    {
        return $this->state(['projet_statut' => StatutProjet::Termine, 'statut_final' => StatutFinalProjet::Succes]);
    }
}
