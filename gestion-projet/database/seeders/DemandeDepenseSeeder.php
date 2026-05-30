<?php

namespace Database\Seeders;

use App\Enums\ModePaiement;
use App\Enums\StatutDemande;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemandeDepenseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDummyFiles();

        $daf = Utilisateur::where('role_key', 'daf')->first();
        $ac = Utilisateur::where('role_key', 'ac')->first();

        // Porteurs chargés par email pour un mapping fiable et explicite
        $porteurs = Utilisateur::where('role_key', 'porteur')
            ->where('utilisateur_actif', true)
            ->get()
            ->keyBy('utilisateur_email');

        $jb = $porteurs['ocheick418@gmail.com'] ?? null;        // BIOTECH-BF + PAES-UJKZ
        $aminata = $porteurs['a.traore@ujkz.bf'] ?? null;       // PRESAR
        $rasmane = $porteurs['r.ouedraogo@ujkz.bf'] ?? null;    // BIODIV-BF
        $salamata = $porteurs['s.sawadogo@ujkz.bf'] ?? null;    // NTIC-EDU
        $boly = $porteurs['s.boly@ujkz.bf'] ?? null;            // ENERGY-SOLAR

        // ── 0a. Demandes terminées pour FORMASUP / Dr. Kaboré (pour taux d'exécution à 99%) ──
        $convFormasup = Convention::where('convention_titre', 'like', '%FORMASUP%')->first();
        $kabore = $porteurs['m.kabore@ujkz.bf'] ?? null;
        if ($convFormasup && $kabore) {
            // Demand 1: "Formations et ateliers pédagogiques"
            $this->creerDemandeParcourue(
                convention: $convFormasup,
                rubriqueLibelle: 'Formations et ateliers pédagogiques',
                porteur: $kabore,
                daf: $daf,
                ac: $ac,
                montant: 100_000_000,
                objet: 'Organisation de 15 sessions de formation pédagogique intensive',
                description: 'Organisation de sessions de renforcement de capacités pédagogiques destinées aux enseignants-chercheurs de l\'UJKZ. Thèmes : méthodes actives, évaluation et conception de syllabus.',
                justificatif: 'justificatifs/formasup-ateliers-proforma.pdf',
                rapport: 'rapports/formasup-ateliers-rapport-reception.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2022-04-15',
                paiement: [
                    'montant' => 100_000_000,
                    'date' => '2022-05-20',
                    'mode' => ModePaiement::Virement,
                    'ref' => 'VIR-UE-2022-0415',
                ],
            );

            // Demand 2: "Développement de ressources numériques"
            $this->creerDemandeParcourue(
                convention: $convFormasup,
                rubriqueLibelle: 'Développement de ressources numériques',
                porteur: $kabore,
                daf: $daf,
                ac: $ac,
                montant: 50_000_000,
                objet: 'Acquisition et mise en place d\'une plateforme LMS moodle et serveurs cloud',
                description: 'Création d\'un espace de cours e-learning Moodle. Hébergement cloud, acquisition de licences de développement d\'outils auteurs, et formation des administrateurs.',
                justificatif: 'justificatifs/formasup-lms-devis.pdf',
                rapport: 'rapports/formasup-lms-rapport.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2022-09-10',
                paiement: [
                    'montant' => 50_000_000,
                    'date' => '2022-10-15',
                    'mode' => ModePaiement::Virement,
                    'ref' => 'VIR-UE-2022-0910',
                ],
            );

            // Direct payment 3: "Audit final externe et évaluation du projet"
            Paiement::create([
                'type_paiement' => 'direct',
                'id_convention' => $convFormasup->id_convention,
                'id_projet' => $convFormasup->id_projet,
                'id_rubrique' => null,
                'paiement_montant' => 28_500_000,
                'paiement_objet' => 'Audit financier final et évaluation d\'impact',
                'paiement_description' => 'Règlement direct par l\'Union Européenne du cabinet international d\'audit KPMG pour l\'audit final du projet et le rapport de clôture d\'impact.',
                'paiement_date' => '2024-08-10',
                'id_enregistreur_paiement' => $kabore->id_utilisateur,
            ]);
        }

        // ── 0b. Demandes pour PAES-UJKZ / Pr. Cheick (ocheick418@gmail.com) ──
        $convPaesBm = Convention::where('convention_titre', 'like', '%BM-PAES%')->first();
        $convPaesAfd = Convention::where('convention_titre', 'like', '%AFD-PAES%')->first();
        if ($jb) {
            // Demand 1: "Réhabilitation des infrastructures" (BM) -> Terminee
            if ($convPaesBm) {
                $this->creerDemandeParcourue(
                    convention: $convPaesBm,
                    rubriqueLibelle: 'Réhabilitation des infrastructures',
                    porteur: $jb,
                    daf: $daf,
                    ac: $ac,
                    montant: 95_000_000,
                    objet: 'Travaux de réhabilitation des amphithéâtres A et B',
                    description: 'Rénovation complète des baies vitrées, réfection de l\'étanchéité de la toiture, pose de nouveaux tableaux interactifs et peinture générale.',
                    justificatif: 'justificatifs/paes-infras-devis.pdf',
                    rapport: 'rapports/paes-infras-rapport-travaux.pdf',
                    status: StatutDemande::Terminee,
                    dateCreation: '2024-02-10',
                    paiement: [
                        'montant' => 95_000_000,
                        'date' => '2024-03-25',
                        'mode' => ModePaiement::Virement,
                        'ref' => 'VIR-BM-2024-PAES1',
                    ],
                );

                // Demand 2: "Équipements et matériels" (BM) -> Terminee
                $this->creerDemandeParcourue(
                    convention: $convPaesBm,
                    rubriqueLibelle: 'Équipements et matériels',
                    porteur: $jb,
                    daf: $daf,
                    ac: $ac,
                    montant: 45_000_000,
                    objet: 'Acquisition de 120 ordinateurs de bureau pour les salles informatiques',
                    description: 'Achat de 120 ordinateurs HP EliteDesk avec écrans 24 pouces pour équiper les salles de travaux pratiques de l\'UJKZ.',
                    justificatif: 'justificatifs/paes-ordis-proforma.pdf',
                    rapport: 'rapports/paes-ordis-rapport-reception.pdf',
                    status: StatutDemande::Terminee,
                    dateCreation: '2024-05-15',
                    paiement: [
                        'montant' => 45_000_000,
                        'date' => '2024-06-20',
                        'mode' => ModePaiement::Virement,
                        'ref' => 'VIR-BM-2024-PAES2',
                    ],
                );

                // Demand 3: "Formation et renforcement de capacités" (BM) -> RapportSoumis (Paid, report submitted)
                $this->creerDemandeParcourue(
                    convention: $convPaesBm,
                    rubriqueLibelle: 'Formation et renforcement de capacités',
                    porteur: $jb,
                    daf: $daf,
                    ac: $ac,
                    montant: 20_000_000,
                    objet: 'Séminaire international sur les nouvelles pédagogies universitaires',
                    description: 'Financement du voyage d\'étude et des indemnités de participation à la formation sur la pédagogie active dispensée à l\'Université de Dakar.',
                    justificatif: 'justificatifs/paes-formation-programme.pdf',
                    rapport: 'rapports/paes-formation-rapport.pdf',
                    status: StatutDemande::RapportSoumis,
                    dateCreation: '2024-08-01',
                    paiement: [
                        'montant' => 20_000_000,
                        'date' => '2024-09-10',
                        'mode' => ModePaiement::Cheque,
                        'ref' => 'CHQ-BM-2024-PAES3',
                    ],
                );

                // Demand 4: "Audit et évaluation" (BM) -> ValideeAgentComptable (Approved AC, awaiting payment)
                $rubriqueAudit = Rubrique::where('id_convention', $convPaesBm->id_convention)
                    ->where('rubrique_libelle', 'like', '%Audit%')
                    ->first();
                if ($rubriqueAudit) {
                    DemandeDepense::create([
                        'id_rubrique' => $rubriqueAudit->id_rubrique,
                        'id_convention' => $convPaesBm->id_convention,
                        'id_porteur' => $jb->id_utilisateur,
                        'demande_montant' => 8_000_000,
                        'demande_objet' => 'Audit financier à mi-parcours de l\'exercice 2024',
                        'demande_description' => 'Prestation de services professionnels du cabinet d\'audit pour l\'évaluation financière de la première année.',
                        'demande_justificatif' => 'justificatifs/paes-audit-facture-proforma.pdf',
                        'demande_statut' => StatutDemande::ValideeAgentComptable,
                        'demande_date_validation_daf' => now()->subDays(10),
                        'id_validateur_daf' => $daf?->id_utilisateur,
                        'demande_date_validation_ac' => now()->subDays(3),
                        'id_validateur_ac' => $ac?->id_utilisateur,
                        'cree_le' => now()->subDays(15),
                    ]);
                }
            }

            // Demand 5: "Achat d'équipements didactiques et numériques" (AFD) -> Soumise (Newly submitted)
            if ($convPaesAfd) {
                $rubriqueDidactique = Rubrique::where('id_convention', $convPaesAfd->id_convention)
                    ->where('rubrique_libelle', 'like', '%didactiques%')
                    ->first();
                if ($rubriqueDidactique) {
                    DemandeDepense::create([
                        'id_rubrique' => $rubriqueDidactique->id_rubrique,
                        'id_convention' => $convPaesAfd->id_convention,
                        'id_porteur' => $jb->id_utilisateur,
                        'demande_montant' => 32_000_000,
                        'demande_objet' => 'Acquisition d\'écrans géants interactifs et projecteurs laser pour 6 amphis',
                        'demande_description' => 'Fourniture et installation de 6 dalles tactiles interactives de 85 pouces et vidéoprojecteurs laser professionnels.',
                        'demande_justificatif' => 'justificatifs/paes-ecrans-devis.pdf',
                        'demande_statut' => StatutDemande::Soumise,
                        'cree_le' => now()->subDays(1),
                    ]);
                }

                // Direct Payment (AFD)
                Paiement::create([
                    'type_paiement' => 'direct',
                    'id_convention' => $convPaesAfd->id_convention,
                    'id_projet' => $convPaesAfd->id_projet,
                    'id_rubrique' => null,
                    'paiement_montant' => 15_000_000,
                    'paiement_objet' => 'Frais de raccordement fibre optique campus — ONATEL',
                    'paiement_description' => 'Paiement direct effectué par l\'AFD auprès d\'ONATEL pour les travaux d\'infrastructures de raccordement fibre.',
                    'paiement_date' => '2024-06-15',
                    'id_enregistreur_paiement' => $jb->id_utilisateur,
                ]);
            }
        }

        // ── 1. Demande terminée — BIOTECH-BF / Pr. Ouédraogo (JB) ────────────
        $this->creerDemandeParcourue(
            convention: Convention::where('convention_titre', 'like', '%BM-BIOTECH%')->first(),
            rubriqueLibelle: 'Réactifs et consommables de laboratoire',
            porteur: $jb,
            daf: $daf,
            ac: $ac,
            montant: 8_500_000,
            objet: 'AgentComptablequisition de réactifs pour les analyses génomiques — Lot 1',
            description: 'Commande de réactifs PCR (Polymerase Chain Reaction) auprès du fournisseur BIORAD pour les analyses de 400 échantillons. '
                ."Inclus : amorces spécifiques, Taq polymérase, tampons de réaction et kits d'extraction d'ADN.",
            justificatif: 'justificatifs/biotech-lot1-facture-proforma.pdf',
            rapport: 'rapports/biotech-lot1-rapport-execution.pdf',
            status: StatutDemande::Terminee,
            dateCreation: '2024-05-10',
            paiement: [
                'montant' => 8_500_000,
                'date' => '2024-06-05',
                'mode' => ModePaiement::Virement,
                'ref' => 'VIR-BM-2024-0410',
            ],
        );

        // ── 2. Demande rapport soumis — BIOTECH-BF / Pr. Ouédraogo (JB) ──────
        $this->creerDemandeParcourue(
            convention: Convention::where('convention_titre', 'like', '%BM-BIOTECH%')->first(),
            rubriqueLibelle: 'Équipements de laboratoire',
            porteur: $jb,
            daf: $daf,
            ac: $ac,
            montant: 22_000_000,
            objet: 'AgentComptablequisition spectrophotomètre UV-Vis et centrifugeuse réfrigérée',
            description: 'AgentComptablehat de deux équipements critiques : (1) spectrophotomètre UV-Vis NanoDrop 2000 pour la quantification des acides nucléiques '
                .'et (2) centrifugeuse réfrigérée Eppendorf 5804R pour la préparation des échantillons biologiques.',
            justificatif: 'justificatifs/biotech-equipements-devis.pdf',
            rapport: 'rapports/biotech-equipements-rapport.pdf',
            status: StatutDemande::RapportSoumis,
            dateCreation: '2024-06-15',
            paiement: [
                'montant' => 22_000_000,
                'date' => '2024-07-22',
                'mode' => ModePaiement::Virement,
                'ref' => 'VIR-BM-2024-0722',
            ],
        );

        // ── 3. Demande validée AC — AFD-BIOTECH (en attente paiement) ────────
        $conv = Convention::where('convention_titre', 'like', '%AFD-BIOTECH%')->first();
        if ($conv && $jb) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%Mission%')
                ->first();
            if ($rubrique) {
                DemandeDepense::create([
                    'id_rubrique' => $rubrique->id_rubrique,
                    'id_convention' => $conv->id_convention,
                    'id_porteur' => $jb->id_utilisateur,
                    'demande_montant' => 6_800_000,
                    'demande_objet' => 'Mission de collecte d\'échantillons — régions du Sahel et du Nord',
                    'demande_description' => 'Mission scientifique de 15 jours dans les régions du Sahel (Dori) et du Nord (Ouahigouya) '
                        ."pour la collecte d'échantillons de sols et de végétaux. Équipe de 4 chercheurs + 2 techniciens. "
                        .'Inclut : transport, hébergement, per diem et frais de collecte terrain.',
                    'demande_justificatif' => 'justificatifs/biotech-mission-ordre-mission.pdf',
                    'demande_statut' => StatutDemande::ValideeAgentComptable,
                    'demande_date_validation_daf' => now()->subDays(12),
                    'id_validateur_daf' => $daf?->id_utilisateur,
                    'demande_date_validation_ac' => now()->subDays(5),
                    'id_validateur_ac' => $ac?->id_utilisateur,
                    'cree_le' => now()->subDays(20),
                ]);
            }
        }

        // ── 4. Demande terminée — NTIC-EDU / Dr. Sawadogo (cycle précédent) ──
        // Status Terminee pour permettre à demande 5 d'exister sur la même convention
        $conv = Convention::where('convention_titre', 'like', '%BM-NTIC%')->first();
        if ($conv && $salamata) {
            $this->creerDemandeParcourue(
                convention: $conv,
                rubriqueLibelle: 'Infrastructure réseau et connectivité',
                porteur: $salamata,
                daf: $daf,
                ac: $ac,
                montant: 35_000_000,
                objet: 'Installation réseau fibre optique — Bâtiment pédagogique UFR/SEA',
                description: "Déploiement d'un réseau LAN fibre optique 10 Gbps dans le bâtiment principal de l'UFR/SEA : "
                    .'câblage structuré Cat6A, baie de brassage 48 ports, 80 prises réseau, switch Cisco Catalyst 2960 '
                    .'et configuration du routage VLAN. Prestataire : TECHNET Burkina Faso.',
                justificatif: 'justificatifs/ntic-fibre-devis-technet.pdf',
                rapport: 'rapports/ntic-fibre-rapport-reception.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2025-03-01',
                paiement: [
                    'montant' => 35_000_000,
                    'date' => '2025-04-18',
                    'mode' => ModePaiement::Virement,
                    'ref' => 'VIR-BM-2025-0418',
                ],
            );
        }

        // ── 5. Demande soumise — NTIC-EDU / Dr. Sawadogo (nouvelle demande) ──
        $conv = Convention::where('convention_titre', 'like', '%BM-NTIC%')->first();
        if ($conv && $salamata) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%Matériel%')
                ->first();
            if ($rubrique) {
                DemandeDepense::create([
                    'id_rubrique' => $rubrique->id_rubrique,
                    'id_convention' => $conv->id_convention,
                    'id_porteur' => $salamata->id_utilisateur,
                    'demande_montant' => 18_500_000,
                    'demande_objet' => 'AgentComptablequisition de 25 ordinateurs portables et 5 serveurs — Salle informatique B3',
                    'demande_description' => "Dotation de la salle informatique B3 de l'UJKZ : 25 laptops Dell Latitude 5540 (i7, 16Go RAM, 512Go SSD) "
                        .'pour les étudiants en master, et 5 serveurs HP ProLiant DL380 pour les TP de virtualisation. '
                        .'Inclut les licences Windows 11 Pro et le déploiement SCCM.',
                    'demande_justificatif' => 'justificatifs/ntic-materiel-bon-commande.pdf',
                    'demande_statut' => StatutDemande::Soumise,
                    'cree_le' => now()->subDays(2),
                ]);
            }
        }

        // ── 6. Demande rejetée DAF — ENERGY-SOLAR / Prof. Boly ──────────────
        $conv = Convention::where('convention_titre', 'like', '%AFD-SOLAR%')->first();
        if ($conv && $boly) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%panneaux%')
                ->first();
            if ($rubrique) {
                DemandeDepense::create([
                    'id_rubrique' => $rubrique->id_rubrique,
                    'id_convention' => $conv->id_convention,
                    'id_porteur' => $boly->id_utilisateur,
                    'demande_montant' => 45_000_000,
                    'demande_objet' => 'AgentComptablequisition 120 panneaux solaires 400Wc — Campus de Koudougou',
                    'demande_description' => "Commande de 120 panneaux solaires monocristallins 400Wc (marque LONGi Solar) pour l'installation "
                        .'sur les toitures du campus de Koudougou. Puissance totale : 48 kWc. '
                        .'Inclut le transport depuis Abidjan et le dédouanement.',
                    'demande_justificatif' => 'justificatifs/solar-panneaux-facture-proforma.pdf',
                    'demande_statut' => StatutDemande::RejeteeDaf,
                    'demande_motif_rejet' => 'Le devis présenté est incomplet : il manque les spécifications techniques détaillées (rendement, garantie constructeur) '
                        .'et le certificat de conformité CEI 61215. Merci de fournir un devis révisé conforme aux exigences du manuel opérationnel du projet.',
                    'cree_le' => now()->subDays(30),
                    'mis_a_jour_le' => now()->subDays(22),
                ]);
            }
        }

        // ── 7. Nouvelle demande après rejet — ENERGY-SOLAR / Prof. Boly ──────
        $conv = Convention::where('convention_titre', 'like', '%AFD-SOLAR%')->first();
        if ($conv && $boly) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%panneaux%')
                ->first();
            if ($rubrique) {
                DemandeDepense::create([
                    'id_rubrique' => $rubrique->id_rubrique,
                    'id_convention' => $conv->id_convention,
                    'id_porteur' => $boly->id_utilisateur,
                    'demande_montant' => 46_200_000,
                    'demande_objet' => 'AgentComptablequisition 120 panneaux solaires 400Wc — Campus Koudougou (dossier révisé)',
                    'demande_description' => 'Dossier révisé suite au rejet DAF du 15/03/2026. '
                        .'Commande de 120 panneaux LONGi Solar Hi-MO6 400Wc avec certificats CEI 61215 et CEI 61730. '
                        .'Garantie produit 12 ans, garantie performance 30 ans. Transport Abidjan-Ouagadougou inclus. '
                        .'Devis N° TECHSUN-BF-2026-089 daté du 08/04/2026.',
                    'demande_justificatif' => 'justificatifs/solar-panneaux-devis-revise.pdf',
                    'demande_statut' => StatutDemande::Soumise,
                    'cree_le' => now()->subDays(4),
                ]);
            }
        }

        // ── 8. Demande terminée — PRESAR / Dr. Traoré (Aminata) ──────────────
        $conv = Convention::where('convention_titre', 'like', '%UEMOA-PRESAR%')->first();
        if ($conv && $aminata) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%Enquête%')
                ->first();
            if ($rubrique) {
                $this->creerDemandeParcourue(
                    convention: $conv,
                    rubriqueLibelle: null,
                    porteur: $aminata,
                    daf: $daf,
                    ac: $ac,
                    montant: 20_000_000,
                    objet: 'Enquête de référence sur la sécurité alimentaire — 5 provinces du Centre-Nord',
                    description: 'Enquête quantitative auprès de 1 200 ménages dans les provinces du Bam, Namentenga, Sanmatenga, Kaya et Kongoussi. '
                        .'Protocole SDAM (Score de Diversité Alimentaire des Ménages). Équipe de 24 enquêteurs + 6 superviseurs formés à Koudougou. '
                        .'Inclut frais de terrain, saisie des données sur ODK et contrôle qualité.',
                    justificatif: 'justificatifs/presar-enquete-protocole-budget.pdf',
                    rapport: 'rapports/presar-enquete-rapport-final.pdf',
                    status: StatutDemande::Terminee,
                    dateCreation: '2023-10-05',
                    paiement: [
                        'montant' => 20_000_000,
                        'date' => '2023-11-20',
                        'mode' => ModePaiement::Cheque,
                        'ref' => 'CHQ-PRESAR-2023-011',
                    ],
                    rubrique: $rubrique,
                );
            }
        }

        // ── 9. Demande soumise — BIODIV-BF / Prof. Rasmané OUÉDRAOGO ─────────
        $conv = Convention::where('convention_titre', 'like', '%BAD-BIODIV%')->first();
        if ($conv && $rasmane) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%Reboisement%')
                ->first();
            if ($rubrique) {
                DemandeDepense::create([
                    'id_rubrique' => $rubrique->id_rubrique,
                    'id_convention' => $conv->id_convention,
                    'id_porteur' => $rasmane->id_utilisateur,
                    'demande_montant' => 25_000_000,
                    'demande_objet' => 'Production de 150 000 plants forestiers — Pépinières de Ouagadougou et Bobo-Dioulasso',
                    'demande_description' => "Production en pépinière de 150 000 plants d'espèces forestières locales (Karité, Néré, Caïlcédrat, Vène) "
                        .'pour le reboisement de 300 hectares dans les forêts classées du Nakambé. '
                        .'Prestataires : AGRO-PEPS Ouagadougou (80 000 plants) et VERDURE-BF Bobo (70 000 plants). '
                        .'Durée : 4 mois (mai-août 2025).',
                    'demande_justificatif' => 'justificatifs/biodiv-pepinieres-contrats.pdf',
                    'demande_statut' => StatutDemande::Soumise,
                    'cree_le' => now()->subDay(),
                ]);
            }
        }

        // ── 10. Paiements directs — BIOTECH-BF / Pr. Ouédraogo (JB) ─────────
        $conv = Convention::where('convention_titre', 'like', '%BM-BIOTECH%')->first();
        if ($conv && $jb) {
            $rubrique = Rubrique::where('id_convention', $conv->id_convention)
                ->where('rubrique_libelle', 'like', '%Réactifs%')
                ->first();

            Paiement::create([
                'type_paiement' => 'direct',
                'id_convention' => $conv->id_convention,
                'id_projet' => $conv->id_projet,
                'id_rubrique' => $rubrique?->id_rubrique,
                'paiement_montant' => 4_200_000,
                'paiement_objet' => 'Règlement direct fournisseur — Azote liquide SONABHY (2 bonbonnes de 50L)',
                'paiement_description' => 'Paiement effectué directement par la Banque Mondiale auprès de SONABHY pour la fourniture '
                    ."d'azote liquide destiné à la conservation des échantillons biologiques à -196°C.",
                'paiement_date' => '2024-05-12',
                'id_enregistreur_paiement' => $jb->id_utilisateur,
            ]);

            Paiement::create([
                'type_paiement' => 'direct',
                'id_convention' => $conv->id_convention,
                'id_projet' => $conv->id_projet,
                'id_rubrique' => null,
                'paiement_montant' => 1_500_000,
                'paiement_objet' => 'Frais de douane — équipements importés USA',
                'paiement_description' => 'Paiement des frais de douane pour le dédouanement du spectrophotomètre NanoDrop 2000 '
                    .'importé des États-Unis (DGTCP/DRF Ouagadougou). Taxe de mise à la consommation + droits TEC CEDEAO.',
                'paiement_date' => '2024-07-30',
                'id_enregistreur_paiement' => $jb->id_utilisateur,
            ]);
        }

        // ── 11. Paiement direct — ENERGY-SOLAR / Prof. Boly ──────────────────
        $conv = Convention::where('convention_titre', 'like', '%AFD-SOLAR%')->first();
        if ($conv && $boly) {
            Paiement::create([
                'type_paiement' => 'direct',
                'id_convention' => $conv->id_convention,
                'id_projet' => $conv->id_projet,
                'id_rubrique' => null,
                'paiement_montant' => 3_800_000,
                'paiement_objet' => 'Frais de mission expert AFD — Évaluation technique mi-parcours',
                'paiement_description' => "Prise en charge directe par l'AFD des frais de mission de l'expert évaluateur M. Laurent Dupont "
                    ."(Ingénieur en énergies renouvelables, Paris) pour la mission d'évaluation technique du projet "
                    .'du 03 au 14 novembre 2025 à Ouagadougou et Koudougou.',
                'paiement_date' => '2025-11-14',
                'id_enregistreur_paiement' => $boly->id_utilisateur,
            ]);
        }
    }

    /**
     * Crée une demande passée par tout ou partie du circuit de validation.
     */
    private function creerDemandeParcourue(
        ?Convention $convention,
        ?string $rubriqueLibelle,
        ?Utilisateur $porteur,
        ?Utilisateur $daf,
        ?Utilisateur $ac,
        int $montant,
        string $objet,
        string $description,
        string $justificatif,
        ?string $rapport,
        StatutDemande $status,
        string $dateCreation,
        ?array $paiement = null,
        ?Rubrique $rubrique = null,
    ): void {
        if (! $convention || ! $porteur) {
            return;
        }

        if (! $rubrique && $rubriqueLibelle) {
            $rubrique = Rubrique::where('id_convention', $convention->id_convention)
                ->where('rubrique_libelle', 'like', "%{$rubriqueLibelle}%")
                ->first();
        }

        if (! $rubrique) {
            return;
        }

        $created = Carbon::parse($dateCreation);

        $data = [
            'id_rubrique' => $rubrique->id_rubrique,
            'id_convention' => $convention->id_convention,
            'id_porteur' => $porteur->id_utilisateur,
            'demande_montant' => $montant,
            'demande_objet' => $objet,
            'demande_description' => $description,
            'demande_justificatif' => $justificatif,
            'demande_statut' => $status,
            'cree_le' => $created,
            'mis_a_jour_le' => $created,
        ];

        if (! in_array($status, [StatutDemande::Soumise, StatutDemande::RejeteeDaf])) {
            $data['demande_date_validation_daf'] = $created->copy()->addDays(5);
            $data['id_validateur_daf'] = $daf?->id_utilisateur;
        }

        if (in_array($status, [StatutDemande::ValideeAgentComptable, StatutDemande::Payee, StatutDemande::RapportSoumis, StatutDemande::Terminee])) {
            $data['demande_date_validation_ac'] = $created->copy()->addDays(10);
            $data['id_validateur_ac'] = $ac?->id_utilisateur;
        }

        if (in_array($status, [StatutDemande::RapportSoumis, StatutDemande::Terminee])) {
            $data['demande_rapport'] = $rapport;
            $data['demande_rapport_valide_daf'] = $status === StatutDemande::Terminee;
            $data['demande_rapport_valide_ac'] = $status === StatutDemande::Terminee;
        }

        $demande = DemandeDepense::create($data);

        if ($paiement && in_array($status, [StatutDemande::Payee, StatutDemande::RapportSoumis, StatutDemande::Terminee])) {
            Paiement::create([
                'id_demande' => $demande->id_demande,
                'id_projet' => $convention->id_projet,
                'id_convention' => $convention->id_convention,
                'id_rubrique' => $rubrique->id_rubrique,
                'paiement_montant' => $paiement['montant'],
                'paiement_date' => $paiement['date'],
                'paiement_mode' => $paiement['mode']->value,
                'paiement_reference' => $paiement['ref'],
                'id_enregistreur_paiement' => $ac?->id_utilisateur,
                'type_paiement' => 'indirect',
                'paiement_objet' => $objet,
            ]);
        }
    }

    private function createDummyFiles(): void
    {
        $minimalPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\n"
            ."xref\n0 4\n0000000000 65535 f \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n190\n%%EOF\n";

        $paths = [
            'justificatifs/biotech-lot1-facture-proforma.pdf',
            'justificatifs/biotech-equipements-devis.pdf',
            'justificatifs/biotech-mission-ordre-mission.pdf',
            'justificatifs/ntic-fibre-devis-technet.pdf',
            'justificatifs/ntic-materiel-bon-commande.pdf',
            'justificatifs/solar-panneaux-facture-proforma.pdf',
            'justificatifs/solar-panneaux-devis-revise.pdf',
            'justificatifs/presar-enquete-protocole-budget.pdf',
            'justificatifs/biodiv-pepinieres-contrats.pdf',
            'justificatifs/formasup-ateliers-proforma.pdf',
            'justificatifs/formasup-lms-devis.pdf',
            'justificatifs/paes-infras-devis.pdf',
            'justificatifs/paes-ordis-proforma.pdf',
            'justificatifs/paes-formation-programme.pdf',
            'justificatifs/paes-audit-facture-proforma.pdf',
            'justificatifs/paes-ecrans-devis.pdf',
            'rapports/biotech-lot1-rapport-execution.pdf',
            'rapports/biotech-equipements-rapport.pdf',
            'rapports/ntic-fibre-rapport-reception.pdf',
            'rapports/presar-enquete-rapport-final.pdf',
            'rapports/formasup-ateliers-rapport-reception.pdf',
            'rapports/formasup-lms-rapport.pdf',
            'rapports/paes-infras-rapport-travaux.pdf',
            'rapports/paes-ordis-rapport-reception.pdf',
            'rapports/paes-formation-rapport.pdf',
        ];

        foreach ($paths as $path) {
            if (! Storage::disk('private')->exists($path)) {
                Storage::disk('private')->put($path, $minimalPdf);
            }
        }
    }
}
