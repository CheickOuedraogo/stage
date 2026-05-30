<?php

namespace Database\Seeders;

use App\Enums\FormeConvention;
use App\Enums\StatutConvention;
use App\Enums\StatutProjet;
use App\Models\Bailleur;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProjetSeeder extends Seeder
{
    public function run(): void
    {
        $porteurs = Utilisateur::where('role_key', 'porteur')
            ->where('utilisateur_actif', true)
            ->orderBy('id_utilisateur')
            ->get()
            ->keyBy('utilisateur_email');

        // ── Bailleurs ─────────────────────────────────────────────────────────
        $bm = Bailleur::create([
            'bailleur_nom' => 'Banque Mondiale',
            'bailleur_sigle' => 'BM',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'International',
            'bailleur_contact' => 'Département Éducation Afrique',
            'bailleur_email' => 'education-africa@worldbank.org',
            'bailleur_telephone' => '+1 202 473 1000',
            'bailleur_adresse' => '1818 H Street, NW, Washington, DC 20433, USA',
            'bailleur_description' => 'La Banque mondiale est une institution financière internationale qui fournit des prêts et des subventions aux gouvernements.',
        ]);

        $afd = Bailleur::create([
            'bailleur_nom' => 'Agence Française de Développement',
            'bailleur_sigle' => 'AFD',
            'bailleur_type' => 'bilatéral',
            'bailleur_pays' => 'France',
            'bailleur_contact' => 'Bureau Ouagadougou',
            'bailleur_email' => 'ouagadougou@afd.fr',
            'bailleur_telephone' => '+226 25 30 60 60',
            'bailleur_adresse' => 'Avenue du Président Sangoulé Lamizana, Ouagadougou',
            'bailleur_description' => "L'AFD est l'institution financière publique française qui met en œuvre la politique de développement de la France.",
        ]);

        $uemoa = Bailleur::create([
            'bailleur_nom' => 'Union Économique et Monétaire Ouest-Africaine',
            'bailleur_sigle' => 'UEMOA',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'Régional',
            'bailleur_contact' => 'Commission UEMOA',
            'bailleur_email' => 'commission@uemoa.int',
            'bailleur_telephone' => '+226 25 32 24 35',
            'bailleur_adresse' => '380 rue Agostino NETO, Ouagadougou 01',
            'bailleur_description' => "L'UEMOA est une organisation intergouvernementale ouest-africaine visant à créer un marché commun entre ses pays membres.",
        ]);

        $bad = Bailleur::create([
            'bailleur_nom' => 'Banque Africaine de Développement',
            'bailleur_sigle' => 'BAD',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'Continental',
            'bailleur_contact' => 'Bureau Burkina Faso',
            'bailleur_email' => 'bf-contact@afdb.org',
            'bailleur_telephone' => '+226 25 37 62 00',
            'bailleur_adresse' => 'Avenue du Président de Gaulle, Ouagadougou',
            'bailleur_description' => 'La BAD est la principale institution de financement du développement en Afrique.',
        ]);

        $ddc = Bailleur::create([
            'bailleur_nom' => 'Direction du Développement et de la Coopération',
            'bailleur_sigle' => 'DDC',
            'bailleur_type' => 'bilatéral',
            'bailleur_pays' => 'Suisse',
            'bailleur_contact' => 'Ambassade de Suisse',
            'bailleur_email' => 'ouagadougou@ddc.admin.ch',
            'bailleur_telephone' => '+226 25 49 85 00',
            'bailleur_adresse' => 'Rue Agostino Neto, Secteur 4, Ouagadougou',
            'bailleur_description' => "La DDC est l'organe de la Confédération suisse responsable de la coopération internationale.",
        ]);

        $ue = Bailleur::create([
            'bailleur_nom' => 'Union Européenne',
            'bailleur_sigle' => 'UE',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'International',
            'bailleur_contact' => 'Délégation UE Burkina Faso',
            'bailleur_email' => 'delegation-burkina-faso@eeas.europa.eu',
            'bailleur_telephone' => '+226 25 49 85 00',
            'bailleur_adresse' => 'Avenue du Président de Gaulle, 01 BP 352, Ouagadougou',
            'bailleur_description' => "La délégation de l'Union européenne représente les institutions de l'UE.",
        ]);

        // ── Projets ───────────────────────────────────────────────────────────
        $projets = $this->creerProjets($porteurs);

        // ── Conventions ───────────────────────────────────────────────────────
        $this->creerConventionsEtRubriques($projets, compact('bm', 'afd', 'uemoa', 'bad', 'ddc', 'ue'));
    }

    /**
     * @param  Collection<string, Utilisateur>  $porteurs  keyed by email
     * @return array<string, Projet>
     */
    private function creerProjets(Collection $porteurs): array
    {
        $data = [
            [
                'sigle' => 'PAES-UJKZ',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Programme d\'Appui à l\'Enseignement Supérieur de l\'UJKZ',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 600_000_000,
                'date_debut' => '2024-01-15',
                'date_fin_prevue' => '2027-01-14',
                'bailleur_description' => "## Présentation\n\nLe **PAES-UJKZ** est un programme stratégique visant à renforcer la qualité et la pertinence de l'enseignement supérieur à l'Université Joseph KI-ZERBO.",
                'objectifs' => "## Objectif général\n\nRenforcer la capacité institutionnelle et pédagogique de l'UJKZ pour offrir une formation de qualité répondant aux besoins du marché du travail.",
                'activites' => "## Plan d'activités\n\n### Composante 1 — Infrastructure\n- Réhabilitation de 3 amphithéâtres\n- Équipement de salles informatiques\n- Mise à niveau des laboratoires",
            ],
            [
                'sigle' => 'PRESAR',
                'porteur_email' => 'a.traore@ujkz.bf',
                'titre' => 'Projet de Recherche sur la Sécurité Alimentaire au Sahel',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 250_000_000,
                'date_debut' => '2023-06-01',
                'date_fin_prevue' => '2026-05-20', // Retard
                'bailleur_description' => "## Présentation\n\nLe **PRESAR** est un projet de recherche interdisciplinaire centré sur l'analyse des systèmes alimentaires sahéliens.",
                'objectifs' => "## Objectifs\n\n1. Cartographier les zones d'insécurité alimentaire au Burkina Faso\n2. Analyser les déterminants de la résilience alimentaire",
                'activites' => "## AgentComptabletivités\n\n### Phase 1 — Diagnostic\n- Enquêtes de terrain dans 5 régions\n- Collecte de données satellitaires",
            ],
            [
                'sigle' => 'FORMASUP',
                'porteur_email' => 'm.kabore@ujkz.bf',
                'titre' => 'Renforcement des Capacités Pédagogiques des Enseignants-Chercheurs du Burkina Faso',
                'status' => StatutProjet::Termine,
                'montant_estime' => 180_000_000,
                'date_debut' => '2022-03-01',
                'date_fin_prevue' => '2024-06-30',
                'date_fin_reelle' => '2024-08-15', // Retard historique
                'bailleur_description' => "## Présentation\n\nLe projet **FORMASUP** avait pour ambition de transformer la pédagogie universitaire au Burkina Faso.",
                'objectifs' => "## Objectifs\n\n1. Former les enseignants-chercheurs aux méthodes actives d'enseignement",
                'activites' => "## AgentComptabletivités réalisées\n\n- Sessions de formation en présentiel (15 sessions)\n- Développement de modules e-learning",
            ],
            [
                'sigle' => 'AQUA-SAHEL',
                'porteur_email' => 'f.zerbo@ujkz.bf',
                'titre' => 'Gestion Intégrée et Durable des Ressources en Eau dans les Provinces Sahéliennes du Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 320_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\n**AQUA-SAHEL** est un projet en attente de financement portant sur la gestion intégrée et durable des ressources en eau.",
                'objectifs' => "## Objectifs\n\n1. Améliorer l'accès à l'eau potable dans 3 provinces",
                'activites' => "## AgentComptabletivités prévues\n\n- Études hydrogéologiques préliminaires",
            ],
            [
                'sigle' => 'INNOV-SANTE',
                'porteur_email' => 'i.bambara@ujkz.bf',
                'titre' => 'Approches Innovantes pour le Renforcement des Systèmes de Santé Communautaire en Milieu Rural',
                'status' => StatutProjet::Annule,
                'montant_estime' => 150_000_000,
                'date_debut' => '2023-09-01',
                'date_fin_prevue' => '2025-08-31',
                'bailleur_description' => "## Présentation\n\nLe projet **INNOV-SANTE** visait à développer des solutions innovantes en santé publique communautaire.",
                'objectifs' => "## Objectifs initiaux\n\n1. Développer des protocoles de prise en charge communautaire",
                'activites' => "## AgentComptabletivités réalisées avant annulation\n\n- Recrutement de l'équipe de projet",
            ],
            [
                'sigle' => 'BIODIV-BF',
                'porteur_email' => 'r.ouedraogo@ujkz.bf',
                'titre' => 'Conservation de la Biodiversité au Burkina Faso',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 500_000_000,
                'date_debut' => '2024-11-01',
                'date_fin_prevue' => '2028-10-31',
                'bailleur_description' => "## Présentation\n\nLe projet **BIODIV-BF** s'attaque à la perte rapide de biodiversité au Burkina Faso.",
                'objectifs' => "## Objectifs\n\n1. Inventorier et cartographier la biodiversité dans 4 zones",
                'activites' => "## Plan d'activités\n\n### Conservation\n- Inventaires biologiques annuels",
            ],
            [
                'sigle' => 'NTIC-EDU',
                'porteur_email' => 's.sawadogo@ujkz.bf',
                'titre' => 'Numérique et Technologies Éducatives pour l\'Université',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 200_000_000,
                'date_debut' => '2025-01-15',
                'date_fin_prevue' => '2027-01-14',
                'bailleur_description' => "## Présentation\n\nLe projet **NTIC-EDU** vise à accélérer la transformation numérique de l'UJKZ.",
                'objectifs' => "## Objectifs\n\n1. Couvrir 100% du campus en WiFi haute vitesse",
                'activites' => "## AgentComptabletivités\n\n- Installation de l'infrastructure réseau\n- Appel d'offres et acquisition des équipements",
            ],
            [
                'sigle' => 'AGRI-SMART',
                'porteur_email' => 'd.nikiema@ujkz.bf',
                'titre' => 'Agriculture Climato-Intelligente et Sécurisation des Revenus Agricoles au Sahel Burkinabè',
                'status' => StatutProjet::Suspendu,
                'montant_estime' => 280_000_000,
                'date_debut' => '2024-07-01',
                'date_fin_prevue' => '2027-06-30',
                'bailleur_description' => "## Présentation\n\nLe projet **AGRI-SMART** développe et diffuse des pratiques agricoles intelligentes.",
                'objectifs' => "## Objectifs\n\n1. Tester et valider 5 technologies CSA sur des parcelles pilotes",
                'activites' => "## AgentComptabletivités\n\n- Installation de stations météo automatiques",
            ],
            [
                'sigle' => 'GENRE-DEV',
                'porteur_email' => 'm.coulibaly@ujkz.bf',
                'titre' => 'Promotion de l\'Égalité de Genre et de l\'Inclusion dans l\'Enseignement Supérieur Burkinabè',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 160_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **GENRE-DEV** vise à promouvoir l'égalité des genres au sein de l'université.",
                'objectifs' => "## Objectifs\n\n1. Sensibiliser 2000 étudiants aux enjeux de genre",
                'activites' => "## AgentComptabletivités prévues\n\n- Diagnostic genre de l'institution",
            ],
            [
                'sigle' => 'ENERGY-SOLAR',
                'porteur_email' => 's.boly@ujkz.bf',
                'titre' => 'Énergie Solaire pour les Campus Universitaires du Sahel',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 350_000_000,
                'date_debut' => '2025-09-01',
                'date_fin_prevue' => '2028-08-31',
                'bailleur_description' => "## Présentation\n\nLe projet **ENERGY-SOLAR** vise à doter l'UJKZ de systèmes d'énergie solaire.",
                'objectifs' => "## Objectifs\n\n1. Installer **500 kWc** de panneaux solaires sur 3 campus",
                'activites' => "## AgentComptabletivités\n\n### Phase 1 — Études et conception",
            ],
            [
                'sigle' => 'BIOTECH-BF',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Biotechnologies Végétales pour la Résistance aux Stress Climatiques au Sahel',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 450_000_000,
                'date_debut' => '2024-04-01',
                'date_fin_prevue' => '2027-03-31',
                'bailleur_description' => "## Présentation\n\nLe projet **BIOTECH-BF** explore les mécanismes moléculaires de tolérance aux stress abiotiques.",
                'objectifs' => "## Objectif général\n\nIdentifier et caractériser les déterminants génétiques.",
                'activites' => "## Plan d'activités\n\n### Composante 1 — Caractérisation des accessions",
            ],
            [
                'sigle' => 'URGENCE-SAHEL',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Intervention d\'Urgence Sanitaire au Sahel',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 100_000_000,
                'date_debut' => '2025-01-01',
                'date_fin_prevue' => '2026-03-31', // Très en retard
                'bailleur_description' => "## Présentation\n\nProjet d'urgence pour la santé au Sahel.",
                'objectifs' => "## Objectifs\n\n1. Répondre aux crises épidémiques.",
                'activites' => "## AgentComptabletivités\n\n- Déploiement d'unités de soin mobiles",
            ],
        ];

        $projets = [];
        foreach ($data as $d) {
            $porteur = $porteurs[$d['porteur_email']] ?? null;
            if (! $porteur) {
                continue;
            }

            $projets[$d['sigle']] = Projet::create([
                'id_porteur' => $porteur->id_utilisateur,
                'projet_titre' => $d['titre'],
                'projet_description' => $d['bailleur_description'],
                'projet_objectifs' => $d['objectifs'],
                'projet_activites' => $d['activites'],
                'projet_montant_estime' => $d['montant_estime'],
                'projet_statut' => $d['status']->value,
                'projet_date_debut' => $d['date_debut'] ?? null,
                'projet_date_fin_prevue' => $d['date_fin_prevue'] ?? null,
                'projet_date_fin_reelle' => $d['date_fin_reelle'] ?? null,
            ]);
        }

        return $projets;
    }

    /**
     * @param  array<string, Projet>  $projets
     * @param  array<string, Bailleur>  $bailleurs
     */
    private function creerConventionsEtRubriques(array $projets, array $bailleurs): void
    {
        $conventions = [
            [
                'projet' => 'PAES-UJKZ',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-PAES-2024-001 — Appui à l\'enseignement supérieur',
                'montant_fcfa' => 300_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-01-10',
                'date_debut' => '2024-01-15',
                'date_fin' => '2027-01-14',
                'bailleur_description' => 'Convention BM.',
                'rubriques' => [
                    ['libelle' => 'Réhabilitation des infrastructures', 'montant_prevu' => 120_000_000],
                    ['libelle' => 'Équipements et matériels', 'montant_prevu' => 80_000_000],
                    ['libelle' => 'Formation et renforcement de capacités', 'montant_prevu' => 60_000_000],
                    ['libelle' => 'Fonctionnement et coordination', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Audit et évaluation', 'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 170_000_000, 'date' => '2024-03-15', 'ref' => 'VRS-BM-2024-001'],
                ],
            ],
            [
                'projet' => 'PAES-UJKZ',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-PAES-2024-002 — Renforcement infrastructurel et pédagogique UJKZ',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-02-15',
                'date_debut' => '2024-03-01',
                'date_fin' => '2027-01-14',
                'bailleur_description' => 'Convention d\'appui complémentaire de l\'AFD pour le PAES-UJKZ.',
                'rubriques' => [
                    ['libelle' => 'Achat d\'équipements didactiques et numériques', 'montant_prevu' => 100_000_000],
                    ['libelle' => 'Travaux de génie civil et raccordement fibre', 'montant_prevu' => 70_000_000],
                    ['libelle' => 'Frais de fonctionnement', 'montant_prevu' => 30_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2024-05-10', 'ref' => 'VRS-AFD-2024-PAES'],
                ],
            ],
            [
                'projet' => 'PRESAR',
                'bailleur' => 'uemoa',
                'titre' => 'Convention UEMOA-PRESAR-2023-001 — Recherche alimentaire',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2023-05-20',
                'date_debut' => '2023-06-01',
                'date_fin' => '2026-05-31',
                'bailleur_description' => 'Subvention UEMOA.',
                'rubriques' => [
                    ['libelle' => 'Enquêtes de terrain et collecte de données', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Analyse et modélisation', 'montant_prevu' => 35_000_000],
                ],
                'versements' => [
                    ['montant' => 45_000_000, 'date' => '2023-07-10', 'ref' => 'VRS-UEMOA-2023-001'],
                ],
            ],
            [
                'projet' => 'FORMASUP',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-FORMASUP-2022-001 — Formation pédagogique',
                'montant_fcfa' => 180_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Terminee,
                'date_signature' => '2022-02-15',
                'date_debut' => '2022-03-01',
                'date_fin' => '2024-07-31',
                'bailleur_description' => 'Convention UE.',
                'rubriques' => [
                    ['libelle' => 'Formations et ateliers pédagogiques', 'montant_prevu' => 70_000_000],
                    ['libelle' => 'Développement de ressources numériques', 'montant_prevu' => 50_000_000],
                ],
                'versements' => [
                    ['montant' => 180_000_000, 'date' => '2022-04-01', 'ref' => 'VRS-UE-2022-001'],
                ],
            ],
            [
                'projet' => 'INNOV-SANTE',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-INNOV-2023-001 — Santé communautaire (ANNULÉE)',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Annulee,
                'date_signature' => '2023-08-10',
                'date_debut' => '2023-09-01',
                'date_fin' => '2025-08-31',
                'bailleur_description' => 'Annulée.',
                'rubriques' => [
                    ['libelle' => 'Études et diagnostics', 'montant_prevu' => 40_000_000],
                ],
                'versements' => [],
            ],
            [
                'projet' => 'BIODIV-BF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-BIODIV-2024-001 — Conservation biodiversité',
                'montant_fcfa' => 420_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-10-01',
                'date_debut' => '2024-11-01',
                'date_fin' => '2028-10-31',
                'bailleur_description' => 'Don BAD.',
                'rubriques' => [
                    ['libelle' => 'Reboisement et restauration des habitats', 'montant_prevu' => 120_000_000],
                ],
                'versements' => [
                    ['montant' => 105_000_000, 'date' => '2025-01-10', 'ref' => 'VRS-BAD-2025-001'],
                ],
            ],
            [
                'projet' => 'URGENCE-SAHEL',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-URGENCE-2025-001 — Urgence sanitaire',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-01-01',
                'date_debut' => '2025-01-01',
                'date_fin' => '2026-03-31', // Retard
                'bailleur_description' => 'Urgence.',
                'rubriques' => [
                    ['libelle' => 'Cliniques mobiles et secours d\'urgence', 'montant_prevu' => 60_000_000],
                ],
                'versements' => [
                    ['montant' => 100_000_000, 'date' => '2025-01-15', 'ref' => 'VRS-BM-2025-URG'],
                ],
            ],
            [
                'projet' => 'BIOTECH-BF',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-BIOTECH-2024-001 — Recherche génomique et biotechnologies végétales',
                'montant_fcfa' => 220_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-03-15',
                'date_debut' => '2024-04-01',
                'date_fin' => '2027-03-31',
                'bailleur_description' => 'Don BM.',
                'rubriques' => [
                    ['libelle' => 'Réactifs et consommables de laboratoire', 'montant_prevu' => 40_000_000],
                    ['libelle' => 'Équipements de laboratoire', 'montant_prevu' => 65_000_000],
                    ['libelle' => 'Missions scientifiques et collaborations', 'montant_prevu' => 35_000_000],
                ],
                'versements' => [
                    ['montant' => 66_000_000, 'date' => '2024-05-10', 'ref' => 'VRS-BM-2024-001'],
                ],
            ],
            [
                'projet' => 'BIOTECH-BF',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-BIOTECH-2024-002 — Terrain et stations expérimentales',
                'montant_fcfa' => 160_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-05-20',
                'date_debut' => '2024-06-01',
                'date_fin' => '2027-03-31',
                'bailleur_description' => 'Don AFD.',
                'rubriques' => [
                    ['libelle' => 'Missions et déplacements', 'montant_prevu' => 30_000_000],
                ],
                'versements' => [
                    ['montant' => 48_000_000, 'date' => '2024-07-15', 'ref' => 'VRS-AFD-2024-001'],
                ],
            ],
            [
                'projet' => 'NTIC-EDU',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-NTIC-2025-001 — Transformation numérique',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-01-05',
                'date_debut' => '2025-01-15',
                'date_fin' => '2027-01-14',
                'bailleur_description' => 'Financement BM.',
                'rubriques' => [
                    ['libelle' => 'Infrastructure réseau et connectivité', 'montant_prevu' => 80_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-03-20', 'ref' => 'VRS-BM-2025-003'],
                ],
            ],
        ];

        foreach ($conventions as $c) {
            $projet = $projets[$c['projet']] ?? null;
            $bailleur = $bailleurs[$c['bailleur']] ?? null;
            if (! $projet || ! $bailleur) {
                continue;
            }

            $convention = Convention::create([
                'id_projet' => $projet->id_projet,
                'id_bailleur' => $bailleur->id_bailleur,
                'convention_titre' => $c['titre'],
                'convention_description' => $c['bailleur_description'],
                'convention_montant' => $c['montant_fcfa'],
                'convention_forme' => $c['forme']->value,
                'convention_devise' => 'XOF',
                'convention_taux_conversion' => 1.0,
                'convention_statut' => $c['status']->value,
                'convention_date_signature' => $c['date_signature'],
                'convention_date_debut' => $c['date_debut'],
                'convention_date_fin' => $c['date_fin'],
            ]);

            foreach ($c['rubriques'] as $r) {
                Rubrique::create([
                    'id_convention' => $convention->id_convention,
                    'rubrique_libelle' => $r['libelle'],
                    'rubrique_montant' => $r['montant_prevu'],
                    'rubrique_description' => null,
                ]);
            }

            foreach ($c['versements'] as $v) {
                Versement::create([
                    'id_convention' => $convention->id_convention,
                    'versement_montant' => $v['montant'],
                    'versement_date_reception' => $v['date'],
                    'versement_reference' => $v['ref'],
                    'versement_description' => null,
                ]);
            }
        }
    }
}
