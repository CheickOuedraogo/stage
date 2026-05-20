<?php

namespace Database\Seeders;

use App\Enums\ConventionForme;
use App\Enums\ConventionStatus;
use App\Enums\ProjectStatus;
use App\Enums\VersementType;
use App\Models\Bailleur;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\User;
use App\Models\Versement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProjetSeeder extends Seeder
{
    public function run(): void
    {
        $porteurs = User::where('utilisateur_role', 'porteur')
            ->where('utilisateur_actif', true)
            ->orderBy('id')
            ->get()
            ->keyBy('email');

        // ── Bailleurs ─────────────────────────────────────────────────────────
        $bm = Bailleur::create([
            'bailleur_nom' => 'Banque Mondiale',
            'bailleur_sigle' => 'BM',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'International',
            'contact' => 'Département Éducation Afrique',
            'email' => 'education-africa@worldbank.org',
            'telephone' => '+1 202 473 1000',
            'adresse' => '1818 H Street, NW, Washington, DC 20433, USA',
            'description' => 'La Banque mondiale est une institution financière internationale qui fournit des prêts et des subventions aux gouvernements de pays à revenus faibles et intermédiaires pour des projets de développement.',
        ]);

        $afd = Bailleur::create([
            'bailleur_nom' => 'Agence Française de Développement',
            'bailleur_sigle' => 'AFD',
            'bailleur_type' => 'bilatéral',
            'bailleur_pays' => 'France',
            'contact' => 'Bureau Ouagadougou',
            'email' => 'ouagadougou@afd.fr',
            'telephone' => '+226 25 30 60 60',
            'adresse' => 'Avenue du Président Aboubakar Sangoulé Lamizana, Ouagadougou',
            'description' => "L'AFD est l'institution financière publique française qui met en œuvre la politique de développement de la France.",
        ]);

        $uemoa = Bailleur::create([
            'bailleur_nom' => 'Union Économique et Monétaire Ouest-Africaine',
            'bailleur_sigle' => 'UEMOA',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'Régional',
            'contact' => 'Commission UEMOA',
            'email' => 'commission@uemoa.int',
            'telephone' => '+226 25 32 24 35',
            'adresse' => '380 rue Agostino NETO, Ouagadougou 01, Burkina Faso',
            'description' => "L'UEMOA est une organisation intergouvernementale ouest-africaine visant à créer un marché commun entre ses pays membres.",
        ]);

        $bad = Bailleur::create([
            'bailleur_nom' => 'Banque Africaine de Développement',
            'bailleur_sigle' => 'BAD',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'Continental',
            'contact' => 'Bureau Burkina Faso',
            'email' => 'bf-contact@afdb.org',
            'telephone' => '+226 25 37 62 00',
            'adresse' => 'Avenue du Président de Gaulle, Ouagadougou',
            'description' => 'La BAD est la principale institution de financement du développement en Afrique, dont la mission est de contribuer au développement économique durable et au progrès social en Afrique.',
        ]);

        $ddc = Bailleur::create([
            'bailleur_nom' => 'Direction du Développement et de la Coopération',
            'bailleur_sigle' => 'DDC',
            'bailleur_type' => 'bilatéral',
            'bailleur_pays' => 'Suisse',
            'contact' => 'Ambassade de Suisse',
            'email' => 'ouagadougou@ddc.admin.ch',
            'telephone' => '+226 25 49 85 00',
            'adresse' => 'Rue Agostino Neto, Secteur 4, Ouagadougou',
            'description' => "La DDC est l'organe de la Confédération suisse responsable de la coopération internationale au développement.",
        ]);

        $ue = Bailleur::create([
            'bailleur_nom' => 'Union Européenne',
            'bailleur_sigle' => 'UE',
            'bailleur_type' => 'multilatéral',
            'bailleur_pays' => 'International',
            'contact' => 'Délégation UE Burkina Faso',
            'email' => 'delegation-burkina-faso@eeas.europa.eu',
            'telephone' => '+226 25 49 85 00',
            'adresse' => 'Avenue du Président de Gaulle, 01 BP 352, Ouagadougou',
            'description' => "La délégation de l'Union européenne représente les institutions de l'UE au Burkina Faso et coordonne les programmes de développement financés par l'UE.",
        ]);

        // ── Projets ───────────────────────────────────────────────────────────
        $projets = $this->creerProjets($porteurs);

        // ── Conventions ───────────────────────────────────────────────────────
        $this->creerConventionsEtRubriques($projets, compact('bm', 'afd', 'uemoa', 'bad', 'ddc', 'ue'));
    }

    /**
     * @param  Collection<string, User>  $porteurs  keyed by email
     * @return array<string, Projet>
     */
    private function creerProjets(Collection $porteurs): array
    {
        $data = [
            [
                'sigle' => 'PAES-UJKZ',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Programme d\'Appui à l\'Enseignement Supérieur de l\'UJKZ',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 500_000_000,
                'date_debut' => '2023-01-15',
                'date_fin_prevue' => '2026-01-14',
                'description' => "## Présentation\n\nLe **PAES-UJKZ** est un programme stratégique visant à renforcer la qualité et la pertinence de l'enseignement supérieur à l'Université Joseph KI-ZERBO.\n\n## Contexte\n\nFace aux défis croissants de massification et de qualité dans l'enseignement supérieur burkinabè, ce programme apporte une réponse structurée et pluridimensionnelle.\n\n## Résultats attendus\n\n- Amélioration du taux de réussite en Licence de **15%**\n- Mise à niveau de **8 laboratoires** de recherche\n- Formation de **120 enseignants-chercheurs**\n- Digitalisation des outils pédagogiques",
                'objectifs' => "## Objectif général\n\nRenforcer la capacité institutionnelle et pédagogique de l'UJKZ pour offrir une formation de qualité répondant aux besoins du marché du travail.\n\n## Objectifs spécifiques\n\n1. Améliorer les infrastructures pédagogiques et de recherche\n2. Renforcer les compétences des enseignants-chercheurs\n3. Développer les partenariats académiques internationaux\n4. Moderniser le système de gestion universitaire",
                'activites' => "## Plan d'activités\n\n### Composante 1 — Infrastructure\n- Réhabilitation de 3 amphithéâtres\n- Équipement de salles informatiques\n- Mise à niveau des laboratoires\n\n### Composante 2 — Renforcement de capacités\n- Formations doctorales en partenariat\n- Stages pédagogiques à l'étranger\n- Ateliers méthodologiques\n\n### Composante 3 — Gouvernance\n- Déploiement d'un logiciel de gestion\n- Audit institutionnel\n- Révision des textes réglementaires",
            ],
            [
                'sigle' => 'PRESAR',
                'porteur_email' => 'a.traore@ujkz.bf',
                'titre' => 'Projet de Recherche sur la Sécurité Alimentaire au Sahel',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 250_000_000,
                'date_debut' => '2023-06-01',
                'date_fin_prevue' => '2025-12-31',
                'description' => "## Présentation\n\nLe **PRESAR** est un projet de recherche interdisciplinaire centré sur l'analyse des systèmes alimentaires sahéliens et la formulation de recommandations politiques.\n\n## Problématique\n\nLe Sahel fait face à une insécurité alimentaire chronique aggravée par les changements climatiques, la croissance démographique et les conflits. Ce projet vise à produire des connaissances opérationnelles pour y répondre.\n\n## Partenaires\n\n- INERA (Institut National de l'Environnement et de Recherches Agricoles)\n- CIRAD (France)\n- Université de Wageningen (Pays-Bas)",
                'objectifs' => "## Objectifs\n\n1. Cartographier les zones d'insécurité alimentaire au Burkina Faso\n2. Analyser les déterminants de la résilience alimentaire\n3. Développer des indicateurs de suivi locaux\n4. Formuler des recommandations de politique publique",
                'activites' => "## Activités\n\n### Phase 1 — Diagnostic\n- Enquêtes de terrain dans 5 régions\n- Collecte de données satellitaires\n- Revue de littérature scientifique\n\n### Phase 2 — Analyse\n- Modélisation statistique\n- Ateliers de validation avec les communautés\n\n### Phase 3 — Diffusion\n- Publication d'un rapport de recherche\n- Conférence internationale\n- Plaidoyer auprès des décideurs",
            ],
            [
                'sigle' => 'FORMASUP',
                'porteur_email' => 'm.kabore@ujkz.bf',
                'titre' => 'Renforcement des Capacités Pédagogiques des Enseignants-Chercheurs du Burkina Faso',
                'status' => ProjectStatus::Termine,
                'montant_estime' => 180_000_000,
                'date_debut' => '2021-03-01',
                'date_fin_prevue' => '2023-06-30',
                'date_fin_reelle' => '2023-07-15',
                'description' => "## Présentation\n\nLe projet **FORMASUP** avait pour ambition de transformer la pédagogie universitaire au Burkina Faso en introduisant des approches innovantes centrées sur l'apprenant.\n\n## Réalisations\n\n- **340 enseignants** formés sur les nouvelles approches pédagogiques\n- **5 manuels de formation** développés et validés\n- Création d'un **centre de ressources pédagogiques** à l'UJKZ\n- **12 universités** bénéficiaires au niveau régional\n\n## Statut\n\nProjet **terminé** avec succès. Rapport final disponible.",
                'objectifs' => "## Objectifs\n\n1. Former les enseignants-chercheurs aux méthodes actives d'enseignement\n2. Développer des ressources pédagogiques numériques\n3. Créer un réseau régional de formateurs en pédagogie universitaire",
                'activites' => "## Activités réalisées\n\n- Sessions de formation en présentiel (15 sessions)\n- Développement de modules e-learning\n- Accompagnement individualisé de 50 enseignants\n- Création d'une bibliothèque numérique de ressources",
            ],
            [
                'sigle' => 'AQUA-SAHEL',
                'porteur_email' => 'f.zerbo@ujkz.bf',
                'titre' => 'Gestion Intégrée et Durable des Ressources en Eau dans les Provinces Sahéliennes du Burkina Faso',
                'status' => ProjectStatus::EnAttenteFinancement,
                'montant_estime' => 320_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'description' => "## Présentation\n\n**AQUA-SAHEL** est un projet en attente de financement portant sur la gestion intégrée et durable des ressources en eau dans les zones sahéliennes du Burkina Faso.\n\n## Enjeux\n\nLa raréfaction des ressources en eau constitue un défi majeur pour le développement durable du Sahel. Ce projet propose une approche innovante combinant technologies modernes et savoirs locaux.\n\n## Innovations prévues\n\n- Systèmes de collecte des eaux pluviales adaptés\n- Cartographie hydrogéologique participative\n- Formation des communautés à la gestion de l'eau",
                'objectifs' => "## Objectifs\n\n1. Améliorer l'accès à l'eau potable dans 3 provinces\n2. Développer des techniques d'irrigation économes en eau\n3. Renforcer les capacités des gestionnaires locaux de l'eau\n4. Produire un atlas hydrogéologique régional",
                'activites' => "## Activités prévues\n\n- Études hydrogéologiques préliminaires\n- Ateliers de co-conception avec les communautés\n- Déploiement de systèmes pilotes\n- Formation et suivi des comités locaux de l'eau",
            ],
            [
                'sigle' => 'INNOV-SANTE',
                'porteur_email' => 'i.bambara@ujkz.bf',
                'titre' => 'Approches Innovantes pour le Renforcement des Systèmes de Santé Communautaire en Milieu Rural',
                'status' => ProjectStatus::Annule,
                'montant_estime' => 150_000_000,
                'date_debut' => '2022-09-01',
                'date_fin_prevue' => '2024-08-31',
                'description' => "## Présentation\n\nLe projet **INNOV-SANTE** visait à développer des solutions innovantes en santé publique communautaire adaptées aux contextes des zones rurales du Burkina Faso.\n\n## Raison de l'annulation\n\nSuite au retrait du bailleur principal en raison de difficultés institutionnelles, le projet a été officiellement annulé en janvier 2023 après 4 mois d'exécution.\n\n## Acquis préservés\n\nMalgré l'annulation, les études de faisabilité réalisées constituent une base documentaire utile pour de futurs projets similaires.",
                'objectifs' => "## Objectifs initiaux\n\n1. Développer des protocoles de prise en charge communautaire\n2. Former 200 agents de santé communautaires\n3. Créer un système d'information sanitaire décentralisé",
                'activites' => "## Activités réalisées avant annulation\n\n- Recrutement de l'équipe de projet\n- Étude de faisabilité dans 2 districts\n- Ateliers de lancement dans 3 provinces",
            ],
            [
                'sigle' => 'BIODIV-BF',
                'porteur_email' => 'r.ouedraogo@ujkz.bf',
                'titre' => 'Conservation de la Biodiversité au Burkina Faso',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 420_000_000,
                'date_debut' => '2022-11-01',
                'date_fin_prevue' => '2026-10-31',
                'description' => "## Présentation\n\nLe projet **BIODIV-BF** s'attaque à la perte rapide de biodiversité au Burkina Faso en combinant recherche scientifique, conservation in situ et implication des communautés locales.\n\n## Contexte\n\nLe Burkina Faso abrite une biodiversité remarquable mais menacée par la déforestation, le changement climatique et la pression anthropique. Ce projet vise à inverser ces tendances.\n\n## Zones d'intervention\n\n- Réserve de Nazinga\n- Forêt classée de Tiogo\n- Zone tampon du parc W\n- Plaine du Sourou",
                'objectifs' => "## Objectifs\n\n1. Inventorier et cartographier la biodiversité dans 4 zones\n2. Établir des corridors écologiques\n3. Développer des activités génératrices de revenus durables\n4. Former 50 éco-gardes communautaires",
                'activites' => "## Plan d'activités\n\n### Conservation\n- Inventaires biologiques annuels\n- Reboisement de 500 ha\n- Surveillance participative des habitats\n\n### Valorisation\n- Développement de l'écotourisme\n- Apiculture durable\n- Cueillette raisonnée de PFNL\n\n### Sensibilisation\n- Campagnes dans 100 villages\n- Formation des enseignants",
            ],
            [
                'sigle' => 'NTIC-EDU',
                'porteur_email' => 's.sawadogo@ujkz.bf',
                'titre' => 'Numérique et Technologies Éducatives pour l\'Université',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 200_000_000,
                'date_debut' => '2024-01-15',
                'date_fin_prevue' => '2026-01-14',
                'description' => "## Présentation\n\nLe projet **NTIC-EDU** vise à accélérer la transformation numérique de l'UJKZ en dotant l'institution d'infrastructures modernes et en formant les acteurs à leur utilisation pédagogique.\n\n## Axes principaux\n\n1. **Infrastructure** : Réseau WiFi campus, datacentre, ENT\n2. **Contenus** : Développement de MOOCs et ressources numériques\n3. **Compétences** : Formation des enseignants et étudiants au numérique",
                'objectifs' => "## Objectifs\n\n1. Couvrir 100% du campus en WiFi haute vitesse\n2. Déployer un Espace Numérique de Travail (ENT)\n3. Créer 30 modules de formation en ligne\n4. Former 500 étudiants aux outils numériques collaboratifs",
                'activites' => "## Activités\n\n- Installation de l'infrastructure réseau\n- Appel d'offres et acquisition des équipements\n- Développement de la plateforme ENT\n- Production des contenus pédagogiques\n- Sessions de formation utilisateurs",
            ],
            [
                'sigle' => 'AGRI-SMART',
                'porteur_email' => 'd.nikiema@ujkz.bf',
                'titre' => 'Agriculture Climato-Intelligente et Sécurisation des Revenus Agricoles au Sahel Burkinabè',
                'status' => ProjectStatus::Suspendu,
                'montant_estime' => 280_000_000,
                'date_debut' => '2022-07-01',
                'date_fin_prevue' => '2025-06-30',
                'description' => "## Présentation\n\nLe projet **AGRI-SMART** développe et diffuse des pratiques agricoles intelligentes face au climat (CSA) adaptées aux conditions sahéliennes du Burkina Faso.\n\n## Raison de la suspension\n\nLe projet a été **suspendu en mars 2024** suite à des difficultés d'accès aux zones d'intervention liées au contexte sécuritaire. La reprise est envisagée sous réserve d'amélioration de la situation.\n\n## Avancement au moment de la suspension\n\n- 45% des activités réalisées\n- 3 sites pilotes opérationnels sur 8 prévus\n- 120 agriculteurs formés sur 300 prévus",
                'objectifs' => "## Objectifs\n\n1. Tester et valider 5 technologies CSA sur des parcelles pilotes\n2. Former 300 agriculteurs aux pratiques adaptées\n3. Développer des outils d'aide à la décision agroclimatique\n4. Influencer les politiques agricoles nationales",
                'activites' => "## Activités\n\n- Installation de stations météo automatiques\n- Formation des agriculteurs (partiellement réalisée)\n- Suivi des parcelles pilotes\n- Documentation et capitalisation",
            ],
            [
                'sigle' => 'GENRE-DEV',
                'porteur_email' => 'm.coulibaly@ujkz.bf',
                'titre' => 'Promotion de l\'Égalité de Genre et de l\'Inclusion dans l\'Enseignement Supérieur Burkinabè',
                'status' => ProjectStatus::EnAttenteFinancement,
                'montant_estime' => 160_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'description' => "## Présentation\n\nLe projet **GENRE-DEV** vise à promouvoir l'égalité des genres au sein de l'université et à former les étudiants à intégrer la dimension genre dans leurs pratiques professionnelles.\n\n## Pertinence\n\nMalgré des progrès notables, les inégalités de genre persistent dans l'enseignement supérieur burkinabè. Les femmes restent sous-représentées parmi les enseignants-chercheurs et dans les filières scientifiques.\n\n## Portée\n\nLe projet interviendra dans 5 UFR de l'UJKZ et visera directement 2 000 étudiants.",
                'objectifs' => "## Objectifs\n\n1. Sensibiliser 2000 étudiants aux enjeux de genre\n2. Former 30 formateurs à l'approche genre\n3. Développer un module de formation genre pour toutes les filières\n4. Améliorer les conditions d'accueil des étudiantes",
                'activites' => "## Activités prévues\n\n- Diagnostic genre de l'institution\n- Développement du curriculum genre\n- Campagnes de sensibilisation\n- Mentorat de jeunes femmes chercheuses",
            ],
            [
                'sigle' => 'ENERGY-SOLAR',
                'porteur_email' => 's.boly@ujkz.bf',
                'titre' => 'Énergie Solaire pour les Campus Universitaires du Sahel',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 350_000_000,
                'date_debut' => '2023-09-01',
                'date_fin_prevue' => '2026-08-31',
                'description' => "## Présentation\n\nLe projet **ENERGY-SOLAR** vise à doter l'UJKZ et deux universités partenaires de systèmes d'énergie solaire pour garantir un approvisionnement électrique fiable et réduire les coûts énergétiques.\n\n## Justification\n\nLes coupures d'électricité fréquentes perturbent gravement l'activité académique et de recherche. L'énergie solaire constitue une solution durable et économique dans le contexte sahélien.\n\n## Sites d'intervention\n\n- Campus de Kossodo (UJKZ)\n- Université Nazi BONI (Bobo-Dioulasso)\n- Institut Supérieur de Technologie (IST)",
                'objectifs' => "## Objectifs\n\n1. Installer **500 kWc** de panneaux solaires sur 3 campus\n2. Réduire la facture énergétique de **60%**\n3. Assurer une alimentation électrique continue pour les laboratoires\n4. Former **15 techniciens** à la maintenance des installations",
                'activites' => "## Activités\n\n### Phase 1 — Études et conception\n- Audit énergétique des trois campus\n- Dimensionnement des installations\n- Appel d'offres international\n\n### Phase 2 — Installation\n- Travaux de génie civil\n- Installation des équipements\n- Tests et mise en service\n\n### Phase 3 — Exploitation\n- Formation des techniciens\n- Mise en place d'un contrat de maintenance\n- Suivi des performances",
            ],
            [
                'sigle' => 'BIOTECH-BF',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Biotechnologies Végétales pour la Résistance aux Stress Climatiques au Sahel',
                'status' => ProjectStatus::EnCours,
                'montant_estime' => 380_000_000,
                'date_debut' => '2023-04-01',
                'date_fin_prevue' => '2026-03-31',
                'description' => "## Présentation\n\nLe projet **BIOTECH-BF** explore les mécanismes moléculaires de tolérance aux stress abiotiques (sécheresse, chaleur, salinité) chez les cultures vivrières sahéliennes (sorgho, mil, niébé). Il vise à identifier des gènes candidats pour l'amélioration variétale.\n\n## Contexte scientifique\n\nFace au changement climatique, les rendements agricoles sahéliens chutent de 5 à 10% par décennie. Les biotechnologies végétales offrent des leviers pour développer des variétés mieux adaptées sans recourir aux OGM.\n\n## Partenaires\n\n- **INERA** — Ouagadougou (données agronomiques terrain)\n- **IRD** — Montpellier (analyse génomique)\n- **Wageningen University** — Pays-Bas (plateforme phénotypage)\n- **Laboratoire de Biologie Moléculaire — UJKZ** (équipe principale)\n\n## Infrastructures mobilisées\n\n- Laboratoire de biologie moléculaire de l'UFR/SVT (UJKZ)\n- Serre de phénotypage de l'INERA/Kamboinsé\n- 3 stations expérimentales (Dori, Fada N'Gourma, Léo)",
                'objectifs' => "## Objectif général\n\nIdentifier et caractériser les déterminants génétiques et moléculaires de la tolérance aux stress abiotiques chez les cultures sahéliennes, en vue de leur valorisation dans les programmes d'amélioration variétale.\n\n## Objectifs spécifiques\n\n1. Constituer une collection de **500 accessions** de sorgho, mil et niébé évaluées sous stress\n2. Identifier **30 gènes candidats** impliqués dans la réponse aux stress\n3. Développer **5 marqueurs moléculaires** utilisables en sélection assistée\n4. Renforcer les capacités de **12 chercheurs et 20 doctorants** en biotechnologies\n5. Produire **15 publications** dans des revues scientifiques internationales à comité de lecture",
                'activites' => "## Plan d'activités\n\n### Composante 1 — Caractérisation des accessions (Années 1-2)\n- Collecte de matériel génétique dans 6 régions agro-écologiques\n- Évaluation phénotypique en conditions contrôlées et en plein champ\n- Extraction ADN et genotypage SNP (500K marqueurs)\n- Analyses GWAS (Genome-Wide Association Studies)\n\n### Composante 2 — Biologie moléculaire (Années 2-3)\n- Séquençage transcriptomique (RNA-seq) sous stress\n- Clonage et caractérisation fonctionnelle des gènes d'intérêt\n- Développement de constructions transgéniques pour la validation\n- Tests de tolérance sur plantes modèles (*Arabidopsis thaliana*)\n\n### Composante 3 — Valorisation et renforcement de capacités\n- Formation de 12 chercheurs en bioinformatique et génomique\n- Encadrement de 8 thèses de doctorat\n- Dépôt de 3 demandes de brevets\n- Organisation d'un symposium international à Ouagadougou",
            ],
        ];

        $projets = [];
        foreach ($data as $d) {
            $porteur = $porteurs[$d['porteur_email']] ?? null;
            if (! $porteur) {
                continue;
            }

            $projets[$d['sigle']] = Projet::create([
                'id_porteur' => $porteur->id,
                'projet_titre' => $d['titre'],
                'projet_description' => $d['description'],
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
                'titre' => 'Convention BM-PAES-2023-001 — Appui à l\'enseignement supérieur',
                'montant_fcfa' => 300_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-01-10',
                'date_debut' => '2023-01-15',
                'date_fin' => '2026-01-14',
                'description' => "Convention de financement à titre de don accordée par la Banque Mondiale pour soutenir le Programme d'Appui à l'Enseignement Supérieur de l'UJKZ.",
                'rubriques' => [
                    ['libelle' => 'Réhabilitation des infrastructures', 'montant_prevu' => 120_000_000],
                    ['libelle' => 'Équipements et matériels', 'montant_prevu' => 80_000_000],
                    ['libelle' => 'Formation et renforcement de capacités', 'montant_prevu' => 60_000_000],
                    ['libelle' => 'Fonctionnement et coordination', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Audit et évaluation', 'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 90_000_000, 'type' => VersementType::Avance, 'date' => '2023-03-15', 'ref' => 'VRS-BM-2023-001'],
                    ['montant' => 75_000_000, 'type' => VersementType::Tranche, 'date' => '2024-01-20', 'ref' => 'VRS-BM-2024-001'],
                    ['montant' => 60_000_000, 'type' => VersementType::Tranche, 'date' => '2024-09-10', 'ref' => 'VRS-BM-2024-002'],
                ],
            ],
            [
                'projet' => 'PAES-UJKZ',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-PAES-2023-002 — Volet pédagogique',
                'montant_fcfa' => 200_000_000,
                'forme' => ConventionForme::Pret,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-02-15',
                'date_debut' => '2023-03-01',
                'date_fin' => '2026-01-14',
                'description' => "Prêt concessionnel de l'AFD destiné au renforcement du volet pédagogique du PAES-UJKZ, notamment la modernisation des méthodes d'enseignement.",
                'rubriques' => [
                    ['libelle' => 'Missions et déplacements internationaux', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Prestations intellectuelles (consultants)', 'montant_prevu' => 80_000_000],
                    ['libelle' => 'Matériel informatique et équipements', 'montant_prevu' => 40_000_000],
                    ['libelle' => 'Communication et dissémination', 'montant_prevu' => 30_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'type' => VersementType::Avance, 'date' => '2023-04-01', 'ref' => 'VRS-AFD-2023-001'],
                    ['montant' => 50_000_000, 'type' => VersementType::Tranche, 'date' => '2024-02-14', 'ref' => 'VRS-AFD-2024-001'],
                ],
            ],
            [
                'projet' => 'PRESAR',
                'bailleur' => 'uemoa',
                'titre' => 'Convention UEMOA-PRESAR-2023-001 — Recherche alimentaire',
                'montant_fcfa' => 150_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-05-20',
                'date_debut' => '2023-06-01',
                'date_fin' => '2025-12-31',
                'description' => 'Subvention UEMOA pour soutenir la recherche sur la sécurité alimentaire dans les pays membres, avec un focus sur le Burkina Faso.',
                'rubriques' => [
                    ['libelle' => 'Enquêtes de terrain et collecte de données', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Analyse et modélisation', 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Publications et dissémination', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Missions et déplacements', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Fonctionnement', 'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 45_000_000, 'type' => VersementType::Avance, 'date' => '2023-07-10', 'ref' => 'VRS-UEMOA-2023-001'],
                    ['montant' => 40_000_000, 'type' => VersementType::Tranche, 'date' => '2024-03-20', 'ref' => 'VRS-UEMOA-2024-001'],
                ],
            ],
            [
                'projet' => 'PRESAR',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-PRESAR-2023-002 — Volet publication',
                'montant_fcfa' => 100_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-07-01',
                'date_debut' => '2023-08-01',
                'date_fin' => '2025-12-31',
                'description' => 'Don de la coopération suisse pour financer le volet publication et valorisation des résultats de recherche du projet PRESAR.',
                'rubriques' => [
                    ['libelle' => 'Édition et publication scientifique', 'montant_prevu' => 40_000_000],
                    ['libelle' => 'Conférences et ateliers', 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Traduction et révision', 'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 30_000_000, 'type' => VersementType::Avance, 'date' => '2023-09-01', 'ref' => 'VRS-DDC-2023-001'],
                ],
            ],
            [
                'projet' => 'FORMASUP',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-FORMASUP-2021-001 — Formation pédagogique',
                'montant_fcfa' => 180_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Terminee,
                'date_signature' => '2021-02-15',
                'date_debut' => '2021-03-01',
                'date_fin' => '2023-07-31',
                'description' => "Convention de financement de l'Union Européenne pour le projet FORMASUP, entièrement décaissée et clôturée.",
                'rubriques' => [
                    ['libelle' => 'Formations et ateliers pédagogiques', 'montant_prevu' => 70_000_000],
                    ['libelle' => 'Développement de ressources numériques', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Équipements informatiques', 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Fonctionnement et coordination', 'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 54_000_000, 'type' => VersementType::Avance, 'date' => '2021-04-01', 'ref' => 'VRS-UE-2021-001'],
                    ['montant' => 63_000_000, 'type' => VersementType::Tranche, 'date' => '2022-04-15', 'ref' => 'VRS-UE-2022-001'],
                    ['montant' => 63_000_000, 'type' => VersementType::Tranche, 'date' => '2023-02-28', 'ref' => 'VRS-UE-2023-001'],
                ],
            ],
            [
                'projet' => 'BIODIV-BF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-BIODIV-2022-001 — Conservation biodiversité',
                'montant_fcfa' => 420_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2022-10-01',
                'date_debut' => '2022-11-01',
                'date_fin' => '2026-10-31',
                'description' => 'Don de la Banque Africaine de Développement pour le projet de conservation de la biodiversité au Burkina Faso.',
                'rubriques' => [
                    ['libelle' => 'Inventaires et études biologiques', 'montant_prevu' => 80_000_000],
                    ['libelle' => 'Reboisement et restauration des habitats', 'montant_prevu' => 120_000_000],
                    ['libelle' => 'Formation des éco-gardes', 'montant_prevu' => 60_000_000],
                    ['libelle' => 'Développement de l\'écotourisme', 'montant_prevu' => 90_000_000],
                    ['libelle' => 'Sensibilisation communautaire', 'montant_prevu' => 45_000_000],
                    ['libelle' => 'Fonctionnement et coordination', 'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 105_000_000, 'type' => VersementType::Avance, 'date' => '2023-01-10', 'ref' => 'VRS-BAD-2023-001'],
                    ['montant' => 84_000_000, 'type' => VersementType::Tranche, 'date' => '2024-01-15', 'ref' => 'VRS-BAD-2024-001'],
                ],
            ],
            [
                'projet' => 'NTIC-EDU',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-NTIC-2024-001 — Transformation numérique',
                'montant_fcfa' => 200_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2024-01-05',
                'date_debut' => '2024-01-15',
                'date_fin' => '2026-01-14',
                'description' => "Financement Banque Mondiale pour la transformation numérique de l'UJKZ dans le cadre du projet NTIC-EDU.",
                'rubriques' => [
                    ['libelle' => 'Infrastructure réseau et connectivité', 'montant_prevu' => 80_000_000],
                    ['libelle' => 'Matériel informatique et serveurs', 'montant_prevu' => 60_000_000],
                    ['libelle' => 'Développement logiciel (ENT)', 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Formation des utilisateurs', 'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'type' => VersementType::Avance, 'date' => '2024-02-20', 'ref' => 'VRS-BM-2024-003'],
                    ['montant' => 50_000_000, 'type' => VersementType::Tranche, 'date' => '2024-11-05', 'ref' => 'VRS-BM-2024-004'],
                ],
            ],
            [
                'projet' => 'ENERGY-SOLAR',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-SOLAR-2023-001 — Énergie solaire campus',
                'montant_fcfa' => 230_000_000,
                'forme' => ConventionForme::Pret,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-08-15',
                'date_debut' => '2023-09-01',
                'date_fin' => '2026-08-31',
                'description' => 'Prêt concessionnel AFD pour le financement des installations solaires sur les campus universitaires sahéliens.',
                'rubriques' => [
                    ['libelle' => 'Acquisition et installation des panneaux solaires', 'montant_prevu' => 140_000_000],
                    ['libelle' => 'Systèmes de stockage (batteries)', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Travaux de génie civil', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Formation des techniciens', 'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 69_000_000, 'type' => VersementType::Avance, 'date' => '2023-10-15', 'ref' => 'VRS-AFD-2023-002'],
                    ['montant' => 57_500_000, 'type' => VersementType::Tranche, 'date' => '2024-04-10', 'ref' => 'VRS-AFD-2024-002'],
                ],
            ],
            [
                'projet' => 'ENERGY-SOLAR',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-SOLAR-2023-002 — Volet formation',
                'montant_fcfa' => 120_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-09-20',
                'date_debut' => '2023-10-01',
                'date_fin' => '2026-08-31',
                'description' => 'Subvention suisse pour le volet formation et transfert de compétences en énergie solaire du projet ENERGY-SOLAR.',
                'rubriques' => [
                    ['libelle' => 'Formation des techniciens locaux', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Documentation et manuels techniques', 'montant_prevu' => 20_000_000],
                    ['libelle' => 'Équipements pédagogiques', 'montant_prevu' => 30_000_000],
                    ['libelle' => 'Fonctionnement', 'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 36_000_000, 'type' => VersementType::Avance, 'date' => '2023-11-01', 'ref' => 'VRS-DDC-2023-002'],
                ],
            ],
            [
                'projet' => 'BIOTECH-BF',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-BIOTECH-2023-001 — Recherche génomique et biotechnologies végétales',
                'montant_fcfa' => 220_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-03-15',
                'date_debut' => '2023-04-01',
                'date_fin' => '2026-03-31',
                'description' => "Don de la Banque Mondiale pour financer les volets génomique, équipements de laboratoire et renforcement de capacités du projet BIOTECH-BF. Cette convention finance spécifiquement la plateforme de génotypage SNP, les équipements d'analyse moléculaire et les missions scientifiques.",
                'rubriques' => [
                    ['libelle' => 'Équipements de laboratoire', 'montant_prevu' => 65_000_000],
                    ['libelle' => 'Réactifs et consommables de laboratoire', 'montant_prevu' => 40_000_000],
                    ['libelle' => 'Missions scientifiques et collaborations', 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Séquençage et bioinformatique', 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Formation et bourses doctorales', 'montant_prevu' => 20_000_000],
                    ['libelle' => 'Fonctionnement et coordination', 'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 66_000_000, 'type' => VersementType::Avance, 'date' => '2023-05-10', 'ref' => 'VRS-BM-2023-005'],
                    ['montant' => 55_000_000, 'type' => VersementType::Tranche, 'date' => '2024-05-20', 'ref' => 'VRS-BM-2024-005'],
                ],
            ],
            [
                'projet' => 'BIOTECH-BF',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-BIOTECH-2023-002 — Terrain et stations expérimentales',
                'montant_fcfa' => 160_000_000,
                'forme' => ConventionForme::Don,
                'status' => ConventionStatus::Active,
                'date_signature' => '2023-05-20',
                'date_debut' => '2023-06-01',
                'date_fin' => '2026-03-31',
                'description' => 'Financement AFD pour les activités de terrain, la gestion des stations expérimentales et les actions de valorisation des résultats du projet BIOTECH-BF dans les zones rurales du Burkina Faso.',
                'rubriques' => [
                    ['libelle' => 'Travaux de terrain et collecte d\'échantillons', 'montant_prevu' => 45_000_000],
                    ['libelle' => 'Missions et déplacements', 'montant_prevu' => 30_000_000],
                    ['libelle' => 'Gestion des stations expérimentales', 'montant_prevu' => 40_000_000],
                    ['libelle' => 'Valorisation et transfert de technologie', 'montant_prevu' => 25_000_000],
                    ['libelle' => 'Dissémination et publications', 'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 48_000_000, 'type' => VersementType::Avance, 'date' => '2023-07-15', 'ref' => 'VRS-AFD-2023-003'],
                    ['montant' => 40_000_000, 'type' => VersementType::Tranche, 'date' => '2024-06-10', 'ref' => 'VRS-AFD-2024-003'],
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
                'id_projet' => $projet->id,
                'id_bailleur' => $bailleur->id,
                'convention_titre' => $c['titre'],
                'convention_description' => $c['description'],
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
                    'id_convention' => $convention->id,
                    'rubrique_libelle' => $r['libelle'],
                    'rubrique_montant_prevu' => $r['montant_prevu'],
                    'rubrique_description' => null,
                ]);
            }

            foreach ($c['versements'] as $v) {
                Versement::create([
                    'id_convention' => $convention->id,
                    'versement_montant' => $v['montant'],
                    'versement_date_reception' => $v['date'],
                    'versement_type' => $v['type']->value,
                    'versement_reference' => $v['ref'],
                    'versement_description' => null,
                ]);
            }
        }
    }
}
