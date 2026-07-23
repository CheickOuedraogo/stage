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
        $porteurs = Utilisateur::query()->where('role_key', 'porteur')
            ->where('utilisateur_actif', true)
            ->orderBy('id_utilisateur', 'asc')
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
            // ── 2 projets conservés ───────────────────────────────────────────
            [
                'sigle' => 'AGRI-TECH-BF',
                'porteur_email' => 'ocheick419@gmail.com',
                'titre' => 'Développement de Technologies Agricoles Intelligentes pour la Résilience Alimentaire au Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 240_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **AGRI-TECH-BF** vise à transformer l'agriculture sahélienne par le déploiement de capteurs IoT à faible coût, d'algorithmes de prédiction climatique et de plateformes mobiles accessibles aux petits exploitants. En combinant intelligence artificielle et savoirs paysans, ce projet ambitionne de réduire de 40% les pertes post-récoltes dans les régions du Centre-Nord et du Sahel.",
                'objectifs' => "## Objectifs\n\n1. Déployer 500 capteurs agro-météo connectés dans 10 provinces\n2. Former 1 500 agriculteurs à l'usage des outils numériques\n3. Développer un système d'alerte précoce contre la sécheresse\n4. Créer 3 incubateurs agri-tech en partenariat avec des coopératives locales",
                'activites' => "## Activités prévues\n\n- Cartographie participative des besoins agricoles par province\n- Conception et prototypage de capteurs adaptés aux conditions sahéliennes\n- Déploiement d'une application mobile multilingue (français, mooré, dioula)\n- Ateliers de co-construction avec les organisations paysannes",
            ],
            [
                'sigle' => 'SANTE-NUM-BF',
                'porteur_email' => 'ocheick419@gmail.com',
                'titre' => 'Renforcement des Systèmes de Santé Communautaires par le Numérique au Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 185_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **SANTE-NUM-BF** entend révolutionner l'accès aux soins primaires dans les zones rurales enclavées en déployant une solution de télémédecine adaptée aux faibles débits internet. En s'appuyant sur un réseau d'agents de santé communautaires équipés de tablettes hors-ligne synchronisables, il vise à couvrir plus de 200 villages aujourd'hui dépourvus de personnel médical qualifié.",
                'objectifs' => "## Objectifs\n\n1. Déployer 150 kits de téléconsultation dans les centres de santé ruraux\n2. Former 300 agents de santé communautaires au triage numérique\n3. Réduire de 30% la mortalité maternelle et infantile dans les zones cibles\n4. Construire une base de données épidémiologique nationale open-source",
                'activites' => "## Activités prévues\n\n- Diagnostic des infrastructures sanitaires de 5 districts prioritaires\n- Développement d'une application de suivi patient offline-first\n- Sessions de formation certifiante pour agents et médecins superviseurs\n- Mise en place d'un tableau de bord de pilotage sanitaire régional",
            ],

            // ── 10 nouveaux projets ───────────────────────────────────────────
            [
                'sigle' => 'EMPLOI-JEUNES',
                'porteur_email' => 'seydou.sanogo@gmail.com',
                'titre' => "Formation Professionnelle et Insertion des Jeunes dans les Filières Porteuses de l'Économie Burkinabè",
                'status' => StatutProjet::EnCours,
                'montant_estime' => 300_000_000,
                'date_debut' => '2025-03-01',
                'date_fin_prevue' => '2028-02-29',
                'bailleur_description' => "## Présentation\n\nLe projet **EMPLOI-JEUNES** vise à répondre à l'urgence de l'emploi des jeunes au Burkina Faso en créant des passerelles efficaces entre la formation professionnelle et le marché du travail. En partenariat avec des entreprises locales et des centres de formation, il déploie des programmes certifiants dans les métiers du numérique, de l'agroalimentaire et des énergies renouvelables.",
                'objectifs' => "## Objectifs\n\n1. Former 5 000 jeunes âgés de 15 à 35 ans aux métiers porteurs\n2. Atteindre un taux d'insertion professionnelle de 70% dans les 6 mois suivant la formation\n3. Créer 200 micro-entreprises accompagnées par un fonds d'amorçage\n4. Équiper 10 centres de formation régionaux en ateliers pratiques",
                'activites' => "## Activités prévues\n\n- Cartographie des besoins en compétences des entreprises par région\n- Conception de 15 parcours de formation certifiants\n- Mise en place de plateformes de stage et d'apprentissage\n- Octroi de bourses et de micro-crédits aux jeunes diplômés\n- Suivi-évaluation et insertion professionnelle",
            ],
            [
                'sigle' => 'ROUTES-RESIL',
                'porteur_email' => 'aissata.ouattara@gmail.com',
                'titre' => 'Construction et Réhabilitation d\'Infrastructures Routières Résilientes aux Changements Climatiques au Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 800_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **ROUTES-RESIL** est un programme d'envergure visant à désenclaver les zones de production agricole du Centre-Nord et de l'Est par la construction de 350 km de routes bitumées et la réhabilitation de 200 km de pistes rurales. Les infrastructures intègrent des techniques de génie civil adaptées aux inondations et à l'érosion, avec des passages à gué dimensionnés pour les crues cycliques.",
                'objectifs' => "## Objectifs\n\n1. Construire 350 km de routes bitumées reliant 12 chefs-lieux de départements\n2. Réhabiliter 200 km de pistes rurales avec des techniques à haute intensité de main-d'œuvre\n3. Réduire de 40% le temps de transport des produits agricoles vers les marchés urbains\n4. Former 500 jeunes aux métiers du BTP et de l'entretien routier",
                'activites' => "## Activités prévues\n\n- Études techniques, environnementales et sociales sur 3 axes prioritaires\n- Appel d'offres et attribution des marchés par lots\n- Construction des sections routières avec drainage climato-adapté\n- Installation de panneaux solaires pour l'éclairage public des tronçons traversant les villages\n- Campagnes de sensibilisation à la sécurité routière",
            ],
            [
                'sigle' => 'E-GOV-BF',
                'porteur_email' => 'karim.sawadogo@gmail.com',
                'titre' => 'Dématérialisation des Services Publics et Gouvernance Ouverte au Burkina Faso',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 220_000_000,
                'date_debut' => '2025-06-01',
                'date_fin_prevue' => '2027-05-31',
                'bailleur_description' => "## Présentation\n\nLe projet **E-GOV-BF** ambitionne de transformer l'administration publique burkinabè en dématérialisant les procédures clés : demande de documents d'état civil, déclaration d'impôts, soumission aux marchés publics et suivi des dossiers administratifs. Une plateforme interopérable centralisée permettra aux citoyens d'accéder aux services depuis un guichet unique numérique.",
                'objectifs' => "## Objectifs\n\n1. Dématérialiser 80% des procédures administratives prioritaires dans 12 ministères\n2. Déployer 150 guichets numériques dans les chefs-lieux de province\n3. Former 2 000 agents publics à l'administration numérique\n4. Publier en open data les données essentielles de l'action publique",
                'activites' => "## Activités prévues\n\n- Audit et réingénierie des processus administratifs cibles\n- Développement de la plateforme e-gov avec signature électronique\n- Sécurisation du système d'information (PSSI, hébergement certifié)\n- Déploiement des guichets dans les préfectures et mairies\n- Campagne de communication et d'alphabétisation numérique citoyenne",
            ],
            [
                'sigle' => 'AGRO-TRANSF',
                'porteur_email' => 'halimatou.diallo@gmail.com',
                'titre' => 'Transformation Agroalimentaire et Développement des Chaînes de Valeur Locales au Burkina Faso',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 450_000_000,
                'date_debut' => '2025-01-15',
                'date_fin_prevue' => '2027-12-31',
                'bailleur_description' => "## Présentation\n\nLe projet **AGRO-TRANSF** vise à moderniser le secteur de la transformation agroalimentaire burkinabè en équipant des unités de transformation à la norme internationale, en développant des labels de qualité et en connectant les producteurs aux marchés régionaux (UEMOA, CEDEAO). L'accent est mis sur les filières à fort potentiel : mangue séchée, beurre de karité, sésame et fonio.",
                'objectifs' => "## Objectifs\n\n1. Équiper 15 unités de transformation aux normes HACCP et ISO 22000\n2. Structurer 50 coopératives agricoles en organisations professionnelles viables\n3. Multiplier par 3 la valeur ajoutée des produits transformés exportés\n4. Certifier 10 produits sous label bio ou commerce équitable",
                'activites' => "## Activités prévues\n\n- Diagnostic technologique des unités de transformation existantes\n- Acquisition et installation d'équipements de séchage, décorticage, conditionnement\n- Formation à l'hygiène, la traçabilité et le contrôle qualité\n- Participation aux salons internationaux (SIAL, BioFach)\n- Mise en place d'un système de financement par fonds de garantie",
            ],
            [
                'sigle' => 'PATRIM-TOUR',
                'porteur_email' => 'boubacar.traore@gmail.com',
                'titre' => 'Valorisation du Patrimoine Culturel et Développement du Tourisme Durable au Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 180_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **PATRIM-TOUR** entend révéler le potentiel touristique méconnu du Burkina Faso en valorisant ses sites classés (ruines de Loropéni, mosquée de Dioulasso-bâ, pictogrammes rupestres de Pobé-Mengao) et en développant un tourisme communautaire respectueux de l'environnement et des cultures locales. Il s'appuie sur le numérique pour la promotion et la gestion des flux touristiques.",
                'objectifs' => "## Objectifs\n\n1. Restaurer et sécuriser 5 sites culturels majeurs inscrits au patrimoine mondial ou national\n2. Créer 10 circuits touristiques intégrés dans 4 régions\n3. Former 300 guides et artisans aux métiers du tourisme durable\n4. Développer une plateforme numérique de réservation et de promotion",
                'activites' => "## Activités prévues\n\n- Travaux de conservation et de mise en valeur des sites\n- Aménagement des accès et des infrastructures d'accueil\n- Création de musées numériques et d'audioguides en langues locales\n- Programmes de micro-crédits pour les coopératives artisanales\n- Marketing territorial via campagnes digitales et salons internationaux",
            ],
            [
                'sigle' => 'CLIM-ADAPT',
                'porteur_email' => 'fatoumata.kone@gmail.com',
                'titre' => 'Renforcement de la Résilience Climatique des Territoires Vulnérables du Liptako-Gourma',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 380_000_000,
                'date_debut' => '2025-04-01',
                'date_fin_prevue' => '2028-03-31',
                'bailleur_description' => "## Présentation\n\nLe projet **CLIM-ADAPT** cible les trois provinces les plus vulnérables aux aléas climatiques de la région du Liptako-Gourma (Soum, Oudalan, Séno). Il combine restauration des terres dégradées, gestion intégrée des ressources en eau, systèmes d'alerte précoce et diversification des moyens d'existence pour renforcer la résilience de 50 000 ménages agro-pastoraux.",
                'objectifs' => "## Objectifs\n\n1. Restaurer 10 000 hectares de terres dégradées par des techniques agro-écologiques\n2. Construire 30 retenues d'eau et 100 km de digues filtrantes\n3. Déployer un système d'alerte précoce multi-risques (sécheresse, inondations, feux de brousse)\n4. Diversifier les revenus de 15 000 ménages via des activités climato-compatibles",
                'activites' => "## Activités prévues\n\n- Diagnostic participatif de vulnérabilité dans 25 communes rurales\n- Reboisement et régénération naturelle assistée des terroirs\n- Construction de seuils d'épandage et de bassins de rétention\n- Distribution de semences résistantes à la sécheresse\n- Assurance climatique indicielle pour les éleveurs et agriculteurs",
            ],
            [
                'sigle' => 'GOUV-MINES',
                'porteur_email' => 'r.ouedraogo@ujkz.bf',
                'titre' => 'Gouvernance Transparente et Responsable du Secteur Minier Artisanal au Burkina Faso',
                'status' => StatutProjet::Suspendu,
                'montant_estime' => 250_000_000,
                'date_debut' => '2024-10-01',
                'date_fin_prevue' => '2027-09-30',
                'bailleur_description' => "## Présentation\n\nLe projet **GOUV-MINES** visait à encadrer le secteur minier artisanal (orpaillage) qui implique plus d'un million de personnes au Burkina Faso. Il ambitionnait de formaliser les sites d'orpaillage, former aux techniques d'extraction sans mercure, et mettre en place des circuits de commercialisation transparents. Le projet a été suspendu en raison de l'insécurité croissante dans les zones d'intervention.",
                'objectifs' => "## Objectifs initiaux\n\n1. Cartographier et géoréférencer 500 sites d'orpaillage artisanal\n2. Former 10 000 orpailleurs aux techniques sans mercure ni cyanure\n3. Créer 50 coopératives minières formelles avec comptes bancaires et accès au crédit\n4. Mettre en place un système de traçabilité de l'or de la mine à l'exportation",
                'activites' => "## Activités réalisées avant suspension\n\n- Recensement et cartographie de 320 sites d'orpaillage\n- Équipement de 8 laboratoires de contrôle de la qualité de l'eau\n- 20 sessions de formation à l'utilisation de la batée et des tables gravimétriques\n- Dialogue politique pour la réforme du code minier artisanal",
            ],
            [
                'sigle' => 'SPORT-JEUNES',
                'porteur_email' => 'm.coulibaly@ujkz.bf',
                'titre' => 'Sport, Jeunesse et Cohésion Sociale dans les Zones à Fort Défi Sécuritaire du Burkina Faso',
                'status' => StatutProjet::EnAttenteFinancement,
                'montant_estime' => 120_000_000,
                'date_debut' => null,
                'date_fin_prevue' => null,
                'bailleur_description' => "## Présentation\n\nLe projet **SPORT-JEUNES** utilise le sport comme vecteur de cohésion sociale, de prévention de l'extrémisme violent et d'insertion des jeunes dans les régions du Nord, du Centre-Nord et de l'Est, fortement impactées par la crise sécuritaire. Il prévoit la réhabilitation d'infrastructures sportives, l'organisation de tournois intercommunautaires et un accompagnement psychosocial par le sport.",
                'objectifs' => "## Objectifs\n\n1. Réhabiliter 20 terrains de sport et espaces jeunes dans les communes vulnérables\n2. Organiser 12 tournois sportifs inter-villages et inter-ethnies par an\n3. Former 200 éducateurs sportifs à la médiation sociale et au secourisme\n4. Toucher 30 000 jeunes par des activités sportives et de dialogue intercommunautaire",
                'activites' => "## Activités prévues\n\n- Réhabilitation et équipement de stades et plateaux multisports\n- Création de ligues sportives communautaires (football, basketball, lutte traditionnelle)\n- Ateliers de sensibilisation aux risques de radicalisation et d'exil\n- Accompagnement psychologique des jeunes vulnérables via le sport\n- Caravanes sportives itinérantes dans les zones d'accueil des PDI",
            ],
            [
                'sigle' => 'NUTRITION-LOC',
                'porteur_email' => 'a.traore@ujkz.bf',
                'titre' => 'Sécurité Nutritionnelle et Valorisation des Filières Alimentaires Locales au Burkina Faso',
                'status' => StatutProjet::Termine,
                'montant_estime' => 200_000_000,
                'date_debut' => '2022-09-01',
                'date_fin_prevue' => '2024-12-31',
                'date_fin_reelle' => '2025-03-15',
                'bailleur_description' => "## Présentation\n\nLe projet **NUTRITION-LOC** a démontré que les filières alimentaires locales (mil, sorgho, niébé, fonio, moringa) peuvent constituer une réponse durable à l'insécurité nutritionnelle chronique au Sahel. En travaillant avec 60 communes rurales, il a développé des aliments enrichis à base de produits locaux, formé les mères à l'équilibre alimentaire et plaidé pour l'intégration des aliments locaux dans les cantines scolaires.",
                'objectifs' => "## Objectifs réalisés\n\n1. Réduire de 25% la prévalence de la malnutrition aiguë dans 60 communes cibles\n2. Développer et certifier 5 aliments de complément à base de produits locaux\n3. Former 2 000 mères éducatrices en nutrition communautaire\n4. Plaider pour l'introduction des aliments locaux dans 500 cantines scolaires",
                'activites' => "## Activités réalisées\n\n- Enquêtes nutritionnelles dans 200 ménages par commune\n- Développement de recettes enrichies avec les groupements féminins\n- Construction de 60 magasins de stockage de produits locaux\n- Campagnes radio et théâtres-forums sur les bonnes pratiques alimentaires\n- Évaluation finale d'impact nutritionnel",
            ],
            [
                'sigle' => 'LAB-IA-BF',
                'porteur_email' => 'ocheick418@gmail.com',
                'titre' => 'Laboratoire d\'Intelligence Artificielle du Burkina Faso',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 500_000_000,
                'date_debut' => '2025-01-15',
                'date_fin_prevue' => '2027-12-31',
                'bailleur_description' => "## Présentation\n\nLe projet **LAB-IA-BF** vise à créer un centre de recherche en intelligence artificielle à Ouagadougou, capable de développer des solutions IA adaptées aux défis du Burkina Faso : agriculture de précision, santé publique, éducation et gouvernance numérique. Sous la direction de **Mr Yilpapoin Ouedraogo**, ingénieur informaticien formé en France, le laboratoire combinera recherche fondamentale, développement de prototypes et formation d'une nouvelle génération d'experts en IA au Sahel.\n\n## Histoire\n\nDe retour dans son pays natal après 10 ans d'expérience en Europe, Mr Ouedraogo a l'ambition de démocratiser l'accès à l'intelligence artificielle pour les pays en développement. En partenariat avec 4 bailleurs internationaux (BAD, UEMOA, AFD, DDC), il a obtenu un financement de 500 millions de FCFA pour bâtir un écosystème IA complet : du serveur au logiciel, de la formation à la recherche appliquée.",
                'objectifs' => "## Objectifs\n\n1. Déployer une infrastructure GPU compute pour l'entraînement de modèles d'IA à grande échelle\n2. Développer des plateformes logicielles d'IA open-source adaptées au contexte ouest-africain\n3. Former 100 ingénieurs spécialisés en IA et Machine Learning\n4. Publier des recherches scientifiques dans les meilleures conférences internationales\n5. Créer des solutions concrètes : reconnaissance d'images agricoles, analyse de données sanitaires, éducation intelligente",
                'activites' => "## Activités prévues\n\n- Installation d'un cluster de serveurs GPU (NVIDIA A100) dans un datacenter local\n- Construction d'un laboratoire de recherche et d'un espace de coworking\n- Développement d'un framework ML léger optimisé pour les données africaines\n- Formation d'experts en deep learning, NLP et computer vision\n- Organisation d'ateliers et conférences sur l'IA au Sahel\n- Partenariats avec les universités burkinabè et internationales",
            ],
            [
                'sigle' => 'FAB-LAB-BF',
                'porteur_email' => 'moussa.coulibaly@gmail.com',
                'titre' => 'Réseau de Laboratoires de Fabrication Numérique et d\'Innovation Technologique pour la Jeunesse Burkinabè',
                'status' => StatutProjet::EnCours,
                'montant_estime' => 280_000_000,
                'date_debut' => '2025-09-01',
                'date_fin_prevue' => '2028-08-31',
                'bailleur_description' => "## Présentation\n\nLe projet **FAB-LAB-BF** déploie un réseau de 10 laboratoires de fabrication numérique (Fab Labs) dans les universités et lycées techniques du Burkina Faso. Équipés d'imprimantes 3D, de découpeuses laser, de fraiseuses CNC et de kits électroniques, ces espaces permettent aux jeunes inventeurs de prototyper des solutions aux défis locaux : drones agricoles, prothèses médicales à bas coût, pompes solaires connectées.",
                'objectifs' => "## Objectifs\n\n1. Créer 10 Fab Labs opérationnels dans 10 villes du Burkina Faso\n2. Former 5 000 jeunes aux technologies de fabrication numérique\n3. Accompagner 100 startups technologiques issues des Fab Labs\n4. Déposer 20 brevets d'invention pour des solutions locales innovantes",
                'activites' => "## Activités prévues\n\n- Aménagement et équipement des espaces Fab Lab (3D, électronique, bois, métal)\n- Recrutement et formation des animateurs Fab Lab\n- Organisation de hackathons et challenges d'innovation ouverte\n- Mise en place d'un fonds d'amorçage pour les projets à fort impact social\n- Connexion au réseau international des Fab Labs (Fab Foundation)",
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
            // ══════════════════════════════════════════════
            //  AGRI-TECH-BF — 240 000 000 FCFA
            // ══════════════════════════════════════════════
            [
                'projet' => 'AGRI-TECH-BF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-AGRI-2024-001 — Financement principal du projet de technologies agricoles intelligentes',
                'montant_fcfa' => 120_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-03-05',
                'date_debut' => '2024-04-01',
                'date_fin' => '2027-03-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt concessionnaire au titre de son guichet agriculture-innovation pour financer le déploiement de la composante technologique du projet AGRI-TECH-BF.",
                'rubriques' => [
                    ['libelle' => 'Infrastructure IoT et capteurs agro-météo',       'montant_prevu' => 55_000_000],
                    ['libelle' => 'Plateforme de traitement de données agricoles',   'montant_prevu' => 30_000_000],
                    ['libelle' => 'Génie civil et raccordement énergie solaire',     'montant_prevu' => 22_000_000],
                    ['libelle' => 'Gestion de projet et audit financier',            'montant_prevu' => 13_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2024-06-01', 'ref' => 'VRS-BAD-AGRI-2024-001'],
                    ['montant' => 36_000_000, 'date' => '2025-03-15', 'ref' => 'VRS-BAD-AGRI-2025-001'],
                ],
            ],
            [
                'projet' => 'AGRI-TECH-BF',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-AGRI-2024-002 — Appui au renforcement des capacités agri-numériques',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-04-18',
                'date_debut' => '2024-05-01',
                'date_fin' => '2027-03-31',
                'bailleur_description' => "## Contexte\n\nDans le cadre du **programme Global Gateway Afrique**, la délégation de l'Union européenne au Burkina Faso octroie une subvention pour financer la composante formation et inclusion numérique du projet.",
                'rubriques' => [
                    ['libelle' => 'Formation des agriculteurs aux outils numériques',         'montant_prevu' => 35_000_000],
                    ['libelle' => 'Développement de l\'application mobile multilingue',       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Incubateurs agri-tech et accompagnement coopératives',     'montant_prevu' => 12_000_000],
                    ['libelle' => 'Communication, suivi-évaluation et rapportage UE',         'montant_prevu' => 8_000_000],
                ],
                'versements' => [
                    ['montant' => 32_000_000, 'date' => '2024-07-10', 'ref' => 'VRS-UE-AGRI-2024-001'],
                ],
            ],
            [
                'projet' => 'AGRI-TECH-BF',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-AGRI-2024-003 — Appui complémentaire à la sécurité alimentaire par le numérique',
                'montant_fcfa' => 40_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-06-01',
                'date_debut' => '2024-07-01',
                'date_fin' => '2026-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Coopération suisse (DDC)** intervient en complément des financements BAD et UE sur la composante recherche-action et capitalisation des savoirs locaux.",
                'rubriques' => [
                    ['libelle' => 'Recherche-action et cartographie participative', 'montant_prevu' => 18_000_000],
                    ['libelle' => 'Documentation et valorisation des savoirs locaux', 'montant_prevu' => 12_000_000],
                    ['libelle' => 'Publications scientifiques et open data',          'montant_prevu' => 7_000_000],
                    ['libelle' => 'Coordination et frais de fonctionnement DDC',      'montant_prevu' => 3_000_000],
                ],
                'versements' => [
                    ['montant' => 20_000_000, 'date' => '2024-08-20', 'ref' => 'VRS-DDC-AGRI-2024-001'],
                    ['montant' => 20_000_000, 'date' => '2025-07-15', 'ref' => 'VRS-DDC-AGRI-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  SANTE-NUM-BF — 185 000 000 FCFA
            // ══════════════════════════════════════════════
            [
                'projet' => 'SANTE-NUM-BF',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-SANTE-2024-001 — Déploiement de la télémédecine rurale au Burkina Faso',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-02-20',
                'date_debut' => '2024-03-15',
                'date_fin' => '2027-03-14',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** finance la composante principale du projet SANTE-NUM-BF dans le cadre de son programme *Santé en Commun*.",
                'rubriques' => [
                    ['libelle' => 'Acquisition et déploiement des kits de téléconsultation',  'montant_prevu' => 45_000_000],
                    ['libelle' => 'Développement de la plateforme offline-first',             'montant_prevu' => 30_000_000],
                    ['libelle' => 'Formation du personnel soignant',                          'montant_prevu' => 15_000_000],
                    ['libelle' => 'Infrastructures réseau et énergie solaire CSPS',           'montant_prevu' => 7_000_000],
                    ['libelle' => 'Gestion de projet, audit et évaluation finale',            'montant_prevu' => 3_000_000],
                ],
                'versements' => [
                    ['montant' => 50_000_000, 'date' => '2024-04-05', 'ref' => 'VRS-AFD-SANTE-2024-001'],
                    ['montant' => 30_000_000, 'date' => '2025-01-20', 'ref' => 'VRS-AFD-SANTE-2025-001'],
                ],
            ],
            [
                'projet' => 'SANTE-NUM-BF',
                'bailleur' => 'uemoa',
                'titre' => 'Convention UEMOA-SANTE-2024-002 — Intégration régionale des systèmes d\'information sanitaire',
                'montant_fcfa' => 50_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-05-10',
                'date_debut' => '2024-06-01',
                'date_fin' => '2026-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Commission de l'UEMOA** accorde une subvention au titre de son programme régional de convergence des systèmes de santé.",
                'rubriques' => [
                    ['libelle' => 'Base de données épidémiologique nationale open-source',   'montant_prevu' => 22_000_000],
                    ['libelle' => 'Interopérabilité avec le SIS régional UEMOA',              'montant_prevu' => 15_000_000],
                    ['libelle' => 'Tableau de bord de pilotage sanitaire régional',           'montant_prevu' => 9_000_000],
                    ['libelle' => 'Ateliers régionaux de partage et harmonisation',           'montant_prevu' => 4_000_000],
                ],
                'versements' => [
                    ['montant' => 25_000_000, 'date' => '2024-07-01', 'ref' => 'VRS-UEMOA-SANTE-2024-001'],
                ],
            ],
            [
                'projet' => 'SANTE-NUM-BF',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-SANTE-2024-003 — Appui à la réduction de la mortalité maternelle et infantile',
                'montant_fcfa' => 35_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2024-09-01',
                'date_debut' => '2024-10-01',
                'date_fin' => '2027-03-14',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** intervient via son fonds fiduciaire *Health Nutrition & Population* pour financer la composante mortalité maternelle et néonatale du projet.",
                'rubriques' => [
                    ['libelle' => 'Module suivi grossesses et accouchements à risque', 'montant_prevu' => 16_000_000],
                    ['libelle' => 'Formation des accoucheuses villageoises au numérique', 'montant_prevu' => 11_000_000],
                    ['libelle' => 'Évaluation d\'impact et rapportage Banque mondiale',   'montant_prevu' => 8_000_000],
                ],
                'versements' => [],
            ],

            // ══════════════════════════════════════════════
            //  EMPLOI-JEUNES — 300 000 000 FCFA
            //  BM 150M + AFD 100M + UE 50M = 300M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'EMPLOI-JEUNES',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-EMPLOI-2025-001 — Appui à la formation professionnelle et à l\'insertion des jeunes',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-02-15',
                'date_debut' => '2025-03-01',
                'date_fin' => '2028-02-29',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** finance la composante principale du projet EMPLOI-JEUNES via son programme *Skills for Youth in the Sahel*. Ce don couvre la mise en place des centres de formation, l'équipement des ateliers et le fonds d'amorçage pour les micro-entreprises.",
                'rubriques' => [
                    ['libelle' => 'Équipement de 10 centres de formation régionaux',          'montant_prevu' => 60_000_000],
                    ['libelle' => 'Conception de 15 parcours certifiants',                    'montant_prevu' => 40_000_000],
                    ['libelle' => 'Fonds d\'amorçage et micro-crédits jeunes',                'montant_prevu' => 30_000_000],
                    ['libelle' => 'Suivi-insertion et évaluation d\'impact',                  'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 75_000_000, 'date' => '2025-04-15', 'ref' => 'VRS-BM-EMPLOI-2025-001'],
                    ['montant' => 45_000_000, 'date' => '2026-03-01', 'ref' => 'VRS-BM-EMPLOI-2026-001'],
                ],
            ],
            [
                'projet' => 'EMPLOI-JEUNES',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-EMPLOI-2025-002 — Appui complémentaire à l\'insertion professionnelle des jeunes',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-03-10',
                'date_debut' => '2025-04-01',
                'date_fin' => '2027-12-31',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** apporte un financement complémentaire dédié à la formation des formateurs, aux bourses d'études pour les jeunes filles et au développement des compétences numériques des jeunes ruraux.",
                'rubriques' => [
                    ['libelle' => 'Formation des formateurs et certification',                'montant_prevu' => 40_000_000],
                    ['libelle' => 'Bourses d\'études pour 500 jeunes filles',                 'montant_prevu' => 35_000_000],
                    ['libelle' => 'Ateliers numériques mobiles en zone rurale',               'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-05-20', 'ref' => 'VRS-AFD-EMPLOI-2025-001'],
                ],
            ],
            [
                'projet' => 'EMPLOI-JEUNES',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-EMPLOI-2025-003 — Appui à l\'entrepreneuriat des jeunes',
                'montant_fcfa' => 50_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-04-01',
                'date_debut' => '2025-05-01',
                'date_fin' => '2027-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance le volet entrepreneuriat via son fonds *Young Entrepreneurs for Africa*.",
                'rubriques' => [
                    ['libelle' => 'Incubateurs et accompagnement de startups',                'montant_prevu' => 25_000_000],
                    ['libelle' => 'Mentorat et mise en réseau avec entreprises',              'montant_prevu' => 15_000_000],
                    ['libelle' => 'Compétitions entrepreneuriales et prix',                   'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 50_000_000, 'date' => '2025-06-10', 'ref' => 'VRS-UE-EMPLOI-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  ROUTES-RESIL — 800 000 000 FCFA
            //  BAD 400M + BM 200M + AFD 200M = 800M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'ROUTES-RESIL',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-ROUTES-2024-001 — Prêt concessionnaire pour les infrastructures routières',
                'montant_fcfa' => 400_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-01-30',
                'date_debut' => '2025-04-01',
                'date_fin' => '2029-03-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt concessionnaire au titre de son guichet infrastructures pour financer la construction des sections routières principales et les études d'impact environnemental.",
                'rubriques' => [
                    ['libelle' => 'Construction de 200 km de routes bitumées',                'montant_prevu' => 180_000_000],
                    ['libelle' => 'Ponts et ouvrages d\'art climato-résilients',               'montant_prevu' => 120_000_000],
                    ['libelle' => 'Études techniques et environnementales',                    'montant_prevu' => 60_000_000],
                    ['libelle' => 'Supervision et contrôle des travaux',                       'montant_prevu' => 40_000_000],
                ],
                'versements' => [
                    ['montant' => 200_000_000, 'date' => '2025-05-15', 'ref' => 'VRS-BAD-ROUTES-2025-001'],
                    ['montant' => 120_000_000, 'date' => '2026-06-01', 'ref' => 'VRS-BAD-ROUTES-2026-001'],
                ],
            ],
            [
                'projet' => 'ROUTES-RESIL',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-ROUTES-2024-002 — Don pour la réhabilitation des pistes rurales',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-02-15',
                'date_debut' => '2025-05-01',
                'date_fin' => '2028-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** finance la réhabilitation des pistes rurales via son *Programme de Développement des Infrastructures Locales*.",
                'rubriques' => [
                    ['libelle' => 'Réhabilitation de 200 km de pistes rurales',               'montant_prevu' => 80_000_000],
                    ['libelle' => 'Haute intensité de main-d\'œuvre (HIMO)',                   'montant_prevu' => 70_000_000],
                    ['libelle' => 'Formation aux métiers du BTP',                              'montant_prevu' => 30_000_000],
                    ['libelle' => 'Suivi communautaire des travaux',                           'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 100_000_000, 'date' => '2025-07-20', 'ref' => 'VRS-BM-ROUTES-2025-001'],
                ],
            ],
            [
                'projet' => 'ROUTES-RESIL',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-ROUTES-2024-003 — Don pour l\'éclairage solaire et la sécurité routière',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-03-01',
                'date_debut' => '2025-06-01',
                'date_fin' => '2028-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** cofinance le volet éclairage solaire et sécurité routière des tronçons traversant les zones habitées.",
                'rubriques' => [
                    ['libelle' => 'Installation d\'éclairage solaire sur 150 km',             'montant_prevu' => 90_000_000],
                    ['libelle' => 'Aménagement de passages piétons et ralentisseurs',          'montant_prevu' => 60_000_000],
                    ['libelle' => 'Campagnes de sensibilisation sécurité routière',            'montant_prevu' => 30_000_000],
                    ['libelle' => 'Évaluation d\'impact socio-économique',                     'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 200_000_000, 'date' => '2025-08-10', 'ref' => 'VRS-AFD-ROUTES-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  E-GOV-BF — 220 000 000 FCFA
            //  UE 120M + AFD 100M = 220M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'E-GOV-BF',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-EGOV-2025-001 — Plateforme e-gouvernance et dématérialisation',
                'montant_fcfa' => 120_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-05-15',
                'date_debut' => '2025-06-01',
                'date_fin' => '2027-05-31',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance le développement de la plateforme e-gouvernance dans le cadre de son programme *Digital Governance for the Sahel*.",
                'rubriques' => [
                    ['libelle' => 'Développement de la plateforme e-gov interopérable',       'montant_prevu' => 50_000_000],
                    ['libelle' => 'Signature électronique et sécurisation des échanges',       'montant_prevu' => 35_000_000],
                    ['libelle' => 'Hébergement cloud souverain et cybersécurité',             'montant_prevu' => 20_000_000],
                    ['libelle' => 'Gestion de projet et assistance technique UE',              'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 72_000_000, 'date' => '2025-07-01', 'ref' => 'VRS-UE-EGOV-2025-001'],
                    ['montant' => 48_000_000, 'date' => '2026-04-01', 'ref' => 'VRS-UE-EGOV-2026-001'],
                ],
            ],
            [
                'projet' => 'E-GOV-BF',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-EGOV-2025-002 — Déploiement des guichets numériques territoriaux',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-06-01',
                'date_debut' => '2025-07-01',
                'date_fin' => '2027-03-31',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** finance le déploiement physique des guichets numériques dans les provinces et la formation des agents publics.",
                'rubriques' => [
                    ['libelle' => 'Déploiement de 150 guichets numériques territoriaux',      'montant_prevu' => 45_000_000],
                    ['libelle' => 'Formation de 2 000 agents publics',                        'montant_prevu' => 30_000_000],
                    ['libelle' => 'Alphabétisation numérique citoyenne',                      'montant_prevu' => 15_000_000],
                    ['libelle' => 'Maintenance et support technique',                         'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-08-15', 'ref' => 'VRS-AFD-EGOV-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  AGRO-TRANSF — 450 000 000 FCFA
            //  BAD 200M + UE 150M + DDC 100M = 450M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'AGRO-TRANSF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-AGRO-2025-001 — Prêt pour la modernisation des unités de transformation',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-01-10',
                'date_debut' => '2025-01-15',
                'date_fin' => '2027-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt pour moderniser les unités de transformation agroalimentaire aux normes internationales.",
                'rubriques' => [
                    ['libelle' => 'Acquisition d\'équipements de transformation',              'montant_prevu' => 80_000_000],
                    ['libelle' => 'Mise aux normes HACCP et ISO 22000',                       'montant_prevu' => 60_000_000],
                    ['libelle' => 'Certification bio et commerce équitable',                  'montant_prevu' => 40_000_000],
                    ['libelle' => 'Assistance technique et contrôle qualité',                  'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 100_000_000, 'date' => '2025-03-01', 'ref' => 'VRS-BAD-AGRO-2025-001'],
                    ['montant' => 60_000_000, 'date' => '2026-02-01', 'ref' => 'VRS-BAD-AGRO-2026-001'],
                ],
            ],
            [
                'projet' => 'AGRO-TRANSF',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-AGRO-2025-002 — Don pour la structuration des filières agricoles',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-02-01',
                'date_debut' => '2025-03-01',
                'date_fin' => '2027-09-30',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance la structuration des coopératives et la mise en marché des produits transformés vers les marchés régionaux.",
                'rubriques' => [
                    ['libelle' => 'Structuration de 50 coopératives agricoles',               'montant_prevu' => 65_000_000],
                    ['libelle' => 'Accès aux marchés UEMOA et CEDEAO',                        'montant_prevu' => 45_000_000],
                    ['libelle' => 'Salons internationaux et marketing',                       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Système de traçabilité et d\'information marché',           'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 75_000_000, 'date' => '2025-04-15', 'ref' => 'VRS-UE-AGRO-2025-001'],
                    ['montant' => 45_000_000, 'date' => '2026-03-15', 'ref' => 'VRS-UE-AGRO-2026-001'],
                ],
            ],
            [
                'projet' => 'AGRO-TRANSF',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-AGRO-2025-003 — Appui à la transformation artisanale féminine',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-03-01',
                'date_debut' => '2025-04-01',
                'date_fin' => '2027-06-30',
                'bailleur_description' => "## Contexte\n\nLa **Coopération suisse (DDC)** finance spécifiquement l'appui aux groupements féminins de transformation du karité et de la mangue séchée, avec un volet genre et autonomisation économique.",
                'rubriques' => [
                    ['libelle' => 'Équipements pour groupements féminins',                    'montant_prevu' => 40_000_000],
                    ['libelle' => 'Formation à la gestion et à la commercialisation',          'montant_prevu' => 30_000_000],
                    ['libelle' => 'Micro-crédits et fonds de roulement',                      'montant_prevu' => 20_000_000],
                    ['libelle' => 'Suivi-évaluation genre et inclusion',                      'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 50_000_000, 'date' => '2025-05-10', 'ref' => 'VRS-DDC-AGRO-2025-001'],
                    ['montant' => 30_000_000, 'date' => '2026-04-10', 'ref' => 'VRS-DDC-AGRO-2026-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  PATRIM-TOUR — 180 000 000 FCFA
            //  UE 100M + AFD 80M = 180M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'PATRIM-TOUR',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-PATRIM-2024-001 — Conservation du patrimoine culturel et développement touristique',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-06-01',
                'date_debut' => '2025-09-01',
                'date_fin' => '2028-08-31',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance la restauration des sites culturels et la création des circuits touristiques via son fonds *Culture and Heritage for Development*.",
                'rubriques' => [
                    ['libelle' => 'Restauration de 5 sites culturels majeurs',                'montant_prevu' => 40_000_000],
                    ['libelle' => 'Création de 10 circuits touristiques intégrés',            'montant_prevu' => 30_000_000],
                    ['libelle' => 'Plateforme numérique de réservation et promotion',         'montant_prevu' => 20_000_000],
                    ['libelle' => 'Études d\'impact et plan de gestion des sites',            'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-10-15', 'ref' => 'VRS-UE-PATRIM-2025-001'],
                ],
            ],
            [
                'projet' => 'PATRIM-TOUR',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-PATRIM-2024-002 — Tourisme communautaire et artisanat local',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-07-01',
                'date_debut' => '2025-10-01',
                'date_fin' => '2028-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** cofinance le volet tourisme communautaire, la formation des guides et le micro-crédit artisanal.",
                'rubriques' => [
                    ['libelle' => 'Formation de 300 guides et artisans',                      'montant_prevu' => 35_000_000],
                    ['libelle' => 'Aménagement des accès et infrastructures d\'accueil',       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Micro-crédits pour coopératives artisanales',              'montant_prevu' => 12_000_000],
                    ['libelle' => 'Musées numériques et audioguides en langues locales',      'montant_prevu' => 8_000_000],
                ],
                'versements' => [
                    ['montant' => 80_000_000, 'date' => '2025-11-20', 'ref' => 'VRS-AFD-PATRIM-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  CLIM-ADAPT — 380 000 000 FCFA
            //  BM 180M + BAD 120M + DDC 80M = 380M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'CLIM-ADAPT',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-CLIM-2025-001 — Résilience climatique et restauration des terres',
                'montant_fcfa' => 180_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-03-15',
                'date_debut' => '2025-04-01',
                'date_fin' => '2028-03-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** finance la composante restauration des terres et systèmes d'alerte précoce via son *Programme de Résilience Climatique au Sahel*.",
                'rubriques' => [
                    ['libelle' => 'Restauration de 10 000 ha de terres dégradées',            'montant_prevu' => 70_000_000],
                    ['libelle' => 'Système d\'alerte précoce multi-risques',                   'montant_prevu' => 50_000_000],
                    ['libelle' => 'Distribution de semences climato-résistantes',             'montant_prevu' => 35_000_000],
                    ['libelle' => 'Assurance climatique indicielle',                          'montant_prevu' => 25_000_000],
                ],
                'versements' => [
                    ['montant' => 90_000_000, 'date' => '2025-05-20', 'ref' => 'VRS-BM-CLIM-2025-001'],
                    ['montant' => 54_000_000, 'date' => '2026-04-15', 'ref' => 'VRS-BM-CLIM-2026-001'],
                ],
            ],
            [
                'projet' => 'CLIM-ADAPT',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-CLIM-2025-002 — Prêt pour les infrastructures hydrauliques',
                'montant_fcfa' => 120_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-04-01',
                'date_debut' => '2025-05-01',
                'date_fin' => '2028-03-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt pour la construction des retenues d'eau et des seuils d'épandage.",
                'rubriques' => [
                    ['libelle' => 'Construction de 30 retenues d\'eau',                       'montant_prevu' => 55_000_000],
                    ['libelle' => '100 km de digues filtrantes et seuils d\'épandage',         'montant_prevu' => 35_000_000],
                    ['libelle' => 'Aménagement de bassins de rétention',                      'montant_prevu' => 20_000_000],
                    ['libelle' => 'Gestion intégrée des ressources en eau',                   'montant_prevu' => 10_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-07-10', 'ref' => 'VRS-BAD-CLIM-2025-001'],
                ],
            ],
            [
                'projet' => 'CLIM-ADAPT',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-CLIM-2025-003 — Diversification des moyens d\'existence',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-05-01',
                'date_debut' => '2025-06-01',
                'date_fin' => '2027-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Coopération suisse (DDC)** finance le volet diversification des revenus et autonomisation des ménages agro-pastoraux vulnérables.",
                'rubriques' => [
                    ['libelle' => 'Activités génératrices de revenus climato-compatibles',     'montant_prevu' => 35_000_000],
                    ['libelle' => 'Formation aux techniques agro-écologiques',                'montant_prevu' => 25_000_000],
                    ['libelle' => 'Fonds de solidarité villageois',                           'montant_prevu' => 12_000_000],
                    ['libelle' => 'Suivi-évaluation participatif',                            'montant_prevu' => 8_000_000],
                ],
                'versements' => [
                    ['montant' => 40_000_000, 'date' => '2025-08-01', 'ref' => 'VRS-DDC-CLIM-2025-001'],
                    ['montant' => 24_000_000, 'date' => '2026-07-01', 'ref' => 'VRS-DDC-CLIM-2026-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  GOUV-MINES — 250 000 000 FCFA
            //  BM 150M + UE 100M = 250M ✓ (SUSPENDU)
            // ══════════════════════════════════════════════
            [
                'projet' => 'GOUV-MINES',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-GOUV-2024-001 — Formalisation et traçabilité du secteur minier artisanal',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Suspendue,
                'date_signature' => '2024-09-01',
                'date_debut' => '2024-10-01',
                'date_fin' => '2027-09-30',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** finance la formalisation du secteur minier artisanal via son programme *Governance for Extractive Industries*. La convention est suspendue pour raisons sécuritaires.",
                'rubriques' => [
                    ['libelle' => 'Cartographie et géoréférencement des sites d\'orpaillage',  'montant_prevu' => 60_000_000],
                    ['libelle' => 'Formation aux techniques sans mercure',                    'montant_prevu' => 40_000_000],
                    ['libelle' => 'Création de coopératives minières formelles',              'montant_prevu' => 30_000_000],
                    ['libelle' => 'Système de traçabilité de l\'or',                          'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 75_000_000, 'date' => '2024-11-01', 'ref' => 'VRS-BM-GOUV-2024-001'],
                    ['montant' => 45_000_000, 'date' => '2025-05-15', 'ref' => 'VRS-BM-GOUV-2025-001'],
                ],
            ],
            [
                'projet' => 'GOUV-MINES',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-GOUV-2024-002 — Réforme du code minier et dialogue politique',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Suspendue,
                'date_signature' => '2024-10-01',
                'date_debut' => '2024-11-01',
                'date_fin' => '2027-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance le volet réforme légale et dialogue politique pour encadrer le secteur minier artisanal. La convention est suspendue en raison de l'insécurité dans les zones d'intervention.",
                'rubriques' => [
                    ['libelle' => 'Appui à la réforme du code minier artisanal',              'montant_prevu' => 40_000_000],
                    ['libelle' => 'Dialogue politique et plaidoyer',                          'montant_prevu' => 30_000_000],
                    ['libelle' => 'Étude d\'impact social et environnemental',                'montant_prevu' => 18_000_000],
                    ['libelle' => 'Communication et transparence',                            'montant_prevu' => 12_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2024-12-01', 'ref' => 'VRS-UE-GOUV-2024-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  SPORT-JEUNES — 120 000 000 FCFA
            //  AFD 70M + UE 50M = 120M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'SPORT-JEUNES',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-SPORT-2025-001 — Infrastructures sportives et cohésion sociale',
                'montant_fcfa' => 70_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-08-01',
                'date_debut' => '2025-10-01',
                'date_fin' => '2028-09-30',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** finance la réhabilitation des infrastructures sportives et la formation des éducateurs sportifs dans le cadre de son programme *Sport et Développement*.",
                'rubriques' => [
                    ['libelle' => 'Réhabilitation de 20 terrains de sport',                   'montant_prevu' => 30_000_000],
                    ['libelle' => 'Équipements sportifs et vestiaires',                       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Formation de 200 éducateurs sportifs',                     'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 42_000_000, 'date' => '2025-11-15', 'ref' => 'VRS-AFD-SPORT-2025-001'],
                ],
            ],
            [
                'projet' => 'SPORT-JEUNES',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-SPORT-2025-002 — Tournois intercommunautaires et prévention de l\'extrémisme',
                'montant_fcfa' => 50_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-09-01',
                'date_debut' => '2025-11-01',
                'date_fin' => '2028-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance les activités de dialogue intercommunautaire par le sport et la prévention de l'extrémisme violent via le fonds *Peace through Sports*.",
                'rubriques' => [
                    ['libelle' => 'Organisation de 12 tournois inter-villages par an',        'montant_prevu' => 20_000_000],
                    ['libelle' => 'Ateliers de sensibilisation et médiation sociale',          'montant_prevu' => 18_000_000],
                    ['libelle' => 'Caravanes sportives itinérantes pour les PDI',             'montant_prevu' => 12_000_000],
                ],
                'versements' => [
                    ['montant' => 50_000_000, 'date' => '2025-12-01', 'ref' => 'VRS-UE-SPORT-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  NUTRITION-LOC — 200 000 000 FCFA (TERMINÉ)
            //  DDC 120M + AFD 80M = 200M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'NUTRITION-LOC',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-NUTRITION-2022-001 — Sécurité nutritionnelle par les filières locales',
                'montant_fcfa' => 120_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Terminee,
                'date_signature' => '2022-08-15',
                'date_debut' => '2022-09-01',
                'date_fin' => '2025-03-15',
                'bailleur_description' => "## Contexte\n\nLa **Coopération suisse (DDC)** a financé la composante principale du projet NUTRITION-LOC dans le cadre de sa stratégie *Sécurité alimentaire Sahel 2025*. La convention est clôturée après évaluation finale.",
                'rubriques' => [
                    ['libelle' => 'Développement d\'aliments enrichis locaux',                 'montant_prevu' => 50_000_000],
                    ['libelle' => 'Formation de 2 000 mères éducatrices en nutrition',        'montant_prevu' => 35_000_000],
                    ['libelle' => 'Construction de 60 magasins de stockage',                  'montant_prevu' => 20_000_000],
                    ['libelle' => 'Évaluation finale d\'impact nutritionnel',                 'montant_prevu' => 15_000_000],
                ],
                'versements' => [
                    ['montant' => 72_000_000, 'date' => '2022-10-01', 'ref' => 'VRS-DDC-NUT-2022-001'],
                    ['montant' => 48_000_000, 'date' => '2023-08-15', 'ref' => 'VRS-DDC-NUT-2023-001'],
                ],
            ],
            [
                'projet' => 'NUTRITION-LOC',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-NUTRITION-2022-002 — Plaidoyer cantines scolaires et communication',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Terminee,
                'date_signature' => '2022-09-01',
                'date_debut' => '2022-10-01',
                'date_fin' => '2024-12-31',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** a cofinancé le volet plaidoyer pour l'introduction des aliments locaux dans les cantines scolaires et les campagnes de communication nutritionnelle.",
                'rubriques' => [
                    ['libelle' => 'Plaidoyer pour 500 cantines scolaires',                    'montant_prevu' => 35_000_000],
                    ['libelle' => 'Campagnes radio et théâtres-forums',                       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Enquêtes nutritionnelles dans 200 ménages',               'montant_prevu' => 12_000_000],
                    ['libelle' => 'Capitalisation et diffusion des résultats',               'montant_prevu' => 8_000_000],
                ],
                'versements' => [
                    ['montant' => 80_000_000, 'date' => '2022-11-01', 'ref' => 'VRS-AFD-NUT-2022-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  LAB-IA-BF — 500 000 000 FCFA
            //  BAD 200M + UEMOA 120M + AFD 100M + DDC 80M = 500M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'LAB-IA-BF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-LABIA-2025-001 — Infrastructure et Équipement du Laboratoire IA',
                'montant_fcfa' => 200_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-01-10',
                'date_debut' => '2025-01-15',
                'date_fin' => '2027-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt concessionnaire au titre de son programme *Innovation Numérique pour le Développement* pour financer l'infrastructure matérielle du laboratoire d'IA : datacenter, serveurs GPU, climatisation et mobilier.",
                'rubriques' => [
                    ['libelle' => 'Serveurs et infrastructure cloud GPU',    'montant_prevu' => 80_000_000],
                    ['libelle' => 'Construction du laboratoire IA',         'montant_prevu' => 80_000_000],
                    ['libelle' => 'Mobilier et équipements',                'montant_prevu' => 40_000_000],
                ],
                'versements' => [
                    ['montant' => 150_000_000, 'date' => '2025-03-01', 'ref' => 'VRS-BAD-LABIA-2025-001'],
                ],
            ],
            [
                'projet' => 'LAB-IA-BF',
                'bailleur' => 'uemoa',
                'titre' => 'Convention UEMOA-LABIA-2025-002 — Développement Logiciel et Plateformes IA',
                'montant_fcfa' => 120_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-02-15',
                'date_debut' => '2025-03-01',
                'date_fin' => '2027-09-30',
                'bailleur_description' => "## Contexte\n\nLa **Commission de l'UEMOA** finance le développement des plateformes logicielles d'IA et l'intégration de données ouvertes ouest-africaines via son programme régional de transformation numérique.",
                'rubriques' => [
                    ['libelle' => 'Développement plateforme Machine Learning',  'montant_prevu' => 60_000_000],
                    ['libelle' => 'Intégration données ouvertes UEMOA',         'montant_prevu' => 30_000_000],
                    ['libelle' => 'Tests, déploiement et maintenance',          'montant_prevu' => 30_000_000],
                ],
                'versements' => [
                    ['montant' => 80_000_000, 'date' => '2025-04-15', 'ref' => 'VRS-UEMOA-LABIA-2025-001'],
                ],
            ],
            [
                'projet' => 'LAB-IA-BF',
                'bailleur' => 'afd',
                'titre' => 'Convention AFD-LABIA-2025-003 — Construction et Formation des Ingénieurs IA',
                'montant_fcfa' => 100_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-03-01',
                'date_debut' => '2025-03-15',
                'date_fin' => '2027-06-30',
                'bailleur_description' => "## Contexte\n\nL'**Agence Française de Développement** finance la construction du bâtiment principal et la formation d'une équipe d'ingénieurs IA dans le cadre de son programme *Innovation et Numérique en Afrique*.",
                'rubriques' => [
                    ['libelle' => 'Bâtiment principal du laboratoire',          'montant_prevu' => 60_000_000],
                    ['libelle' => 'Formation des ingénieurs IA',                'montant_prevu' => 40_000_000],
                ],
                'versements' => [
                    ['montant' => 60_000_000, 'date' => '2025-05-01', 'ref' => 'VRS-AFD-LABIA-2025-001'],
                ],
            ],
            [
                'projet' => 'LAB-IA-BF',
                'bailleur' => 'ddc',
                'titre' => 'Convention DDC-LABIA-2025-004 — Recherche Appliquée et Diffusion Scientifique',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-04-01',
                'date_debut' => '2025-05-01',
                'date_fin' => '2027-12-31',
                'bailleur_description' => "## Contexte\n\nLa **Coopération suisse (DDC)** finance les activités de recherche appliquée et de diffusion scientifique du laboratoire, incluant les publications, les conférences et les partenariats académiques internationaux.",
                'rubriques' => [
                    ['libelle' => 'Projets de recherche appliquée IA',          'montant_prevu' => 50_000_000],
                    ['libelle' => 'Publications et conférences scientifiques',  'montant_prevu' => 30_000_000],
                ],
                'versements' => [
                    ['montant' => 40_000_000, 'date' => '2025-06-01', 'ref' => 'VRS-DDC-LABIA-2025-001'],
                ],
            ],

            // ══════════════════════════════════════════════
            //  FAB-LAB-BF — 280 000 000 FCFA
            //  BAD 150M + BM 80M + UE 50M = 280M ✓
            // ══════════════════════════════════════════════
            [
                'projet' => 'FAB-LAB-BF',
                'bailleur' => 'bad',
                'titre' => 'Convention BAD-FABLAB-2025-001 — Prêt pour les infrastructures Fab Lab',
                'montant_fcfa' => 150_000_000,
                'forme' => FormeConvention::Pret,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-08-15',
                'date_debut' => '2025-09-01',
                'date_fin' => '2028-08-31',
                'bailleur_description' => "## Contexte\n\nLa **Banque Africaine de Développement** accorde un prêt pour l'aménagement et l'équipement des 10 Fab Labs dans le cadre de son programme *Innovation et Emploi des Jeunes*.",
                'rubriques' => [
                    ['libelle' => 'Aménagement de 10 espaces Fab Lab',                       'montant_prevu' => 65_000_000],
                    ['libelle' => 'Équipements 3D, CNC et électronique',                     'montant_prevu' => 40_000_000],
                    ['libelle' => 'Licences logicielles et infrastructure IT',                'montant_prevu' => 25_000_000],
                    ['libelle' => 'Recrutement et formation des animateurs',                  'montant_prevu' => 20_000_000],
                ],
                'versements' => [
                    ['montant' => 75_000_000, 'date' => '2025-10-15', 'ref' => 'VRS-BAD-FAB-2025-001'],
                    ['montant' => 45_000_000, 'date' => '2026-09-01', 'ref' => 'VRS-BAD-FAB-2026-001'],
                ],
            ],
            [
                'projet' => 'FAB-LAB-BF',
                'bailleur' => 'bm',
                'titre' => 'Convention BM-FABLAB-2025-002 — Don pour l\'accompagnement des startups',
                'montant_fcfa' => 80_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-09-01',
                'date_debut' => '2025-10-01',
                'date_fin' => '2028-06-30',
                'bailleur_description' => "## Contexte\n\nLa **Banque mondiale** finance le fonds d'amorçage pour les startups issues des Fab Labs et l'accompagnement à la propriété intellectuelle.",
                'rubriques' => [
                    ['libelle' => 'Fonds d\'amorçage pour 100 startups',                     'montant_prevu' => 35_000_000],
                    ['libelle' => 'Accompagnement au dépôt de brevets',                       'montant_prevu' => 25_000_000],
                    ['libelle' => 'Hackathons et challenges d\'innovation',                   'montant_prevu' => 12_000_000],
                    ['libelle' => 'Connexion au réseau international Fab Foundation',         'montant_prevu' => 8_000_000],
                ],
                'versements' => [
                    ['montant' => 48_000_000, 'date' => '2025-11-20', 'ref' => 'VRS-BM-FAB-2025-001'],
                ],
            ],
            [
                'projet' => 'FAB-LAB-BF',
                'bailleur' => 'ue',
                'titre' => 'Convention UE-FABLAB-2025-003 — Formation aux technologies de fabrication numérique',
                'montant_fcfa' => 50_000_000,
                'forme' => FormeConvention::Don,
                'status' => StatutConvention::Active,
                'date_signature' => '2025-10-01',
                'date_debut' => '2025-11-01',
                'date_fin' => '2028-03-31',
                'bailleur_description' => "## Contexte\n\nL'**Union européenne** finance le volet formation et certification des jeunes aux technologies de fabrication numérique via le programme *Digital Skills for the Sahel*.",
                'rubriques' => [
                    ['libelle' => 'Formation de 5 000 jeunes au numérique',                   'montant_prevu' => 20_000_000],
                    ['libelle' => 'Certifications aux métiers du Fab Lab',                    'montant_prevu' => 18_000_000],
                    ['libelle' => 'Mobilité et échanges avec les Fab Labs européens',         'montant_prevu' => 12_000_000],
                ],
                'versements' => [
                    ['montant' => 50_000_000, 'date' => '2025-12-15', 'ref' => 'VRS-UE-FAB-2025-001'],
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
