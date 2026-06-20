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

        $daf = Utilisateur::query()->where('role_key', 'daf')->first();
        $ac = Utilisateur::query()->where('role_key', 'ac')->first();

        // Porteurs chargés par email pour un mapping fiable et explicite
        $porteurs = Utilisateur::query()->where('role_key', 'porteur')
            ->where('utilisateur_actif', true)
            ->get()
            ->keyBy('utilisateur_email');

        // ==== 1. PROJET TERMINE AVEC 100% D'EXECUTION : NUTRITION-LOC ====
        $convDdcNut = Convention::query()->where('convention_titre', 'like', '%DDC-NUTRITION%')->first();
        $convAfdNut = Convention::query()->where('convention_titre', 'like', '%AFD-NUTRITION%')->first();
        $aTraore = $porteurs['a.traore@ujkz.bf'] ?? null;

        if ($convDdcNut && $aTraore) {
            // DDC - Rubrique 1 : Développement d'aliments (50M)
            $this->creerDemandeParcourue(
                convention: $convDdcNut,
                rubriqueLibelle: 'aliments enrichis',
                porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 50_000_000,
                objet: 'Recherche et développement d\'aliments enrichis à base de mil et niébé',
                description: 'Prestation du laboratoire de biochimie pour le développement et la certification de 5 nouvelles formules de compléments alimentaires locaux.',
                justificatif: 'justificatifs/nut-dev-aliments.pdf',
                rapport: 'rapports/nut-dev-aliments-rapport.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2022-10-15',
                paiement: ['montant' => 50_000_000, 'date' => '2022-11-20', 'mode' => ModePaiement::Virement, 'ref' => 'VIR-DDC-NUT-001']
            );

            // DDC - Rubrique 2 : Formation de mères éducatrices (35M)
            $this->creerDemandeParcourue(
                convention: $convDdcNut,
                rubriqueLibelle: 'mères éducatrices',
                porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 35_000_000,
                objet: 'Sessions de formation de 2000 mères éducatrices',
                description: 'Organisation de 40 sessions de formation dans les communes rurales. Inclut per diem, logistique, et kits de démonstration culinaire.',
                justificatif: 'justificatifs/nut-formation-meres.pdf',
                rapport: 'rapports/nut-formation-meres-rapport.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2023-02-10',
                paiement: ['montant' => 35_000_000, 'date' => '2023-03-05', 'mode' => ModePaiement::Cheque, 'ref' => 'CHQ-DDC-NUT-002']
            );

            // DDC - Rubrique 3 : Magasins de stockage (20M)
            $this->creerDemandeParcourue(
                convention: $convDdcNut,
                rubriqueLibelle: 'magasins de stockage',
                porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 20_000_000,
                objet: 'Construction de 60 petits magasins communautaires de stockage',
                description: 'Travaux de construction des magasins avec les artisans locaux (matériaux locaux, banco stabilisé).',
                justificatif: 'justificatifs/nut-magasins.pdf',
                rapport: 'rapports/nut-magasins-rapport.pdf',
                status: StatutDemande::Terminee,
                dateCreation: '2023-05-15',
                paiement: ['montant' => 20_000_000, 'date' => '2023-06-20', 'mode' => ModePaiement::Virement, 'ref' => 'VIR-DDC-NUT-003']
            );

            // DDC - Rubrique 4 : Évaluation finale (15M) -> Paiement direct
            $rubEval = Rubrique::query()->where('id_convention', $convDdcNut->id_convention)->where('rubrique_libelle', 'like', '%Évaluation%')->first();
            if ($rubEval) {
                Paiement::create([
                    'type_paiement' => 'direct', 'id_convention' => $convDdcNut->id_convention, 'id_projet' => $convDdcNut->id_projet, 'id_rubrique' => $rubEval->id_rubrique,
                    'paiement_montant' => 15_000_000, 'paiement_date' => '2024-12-10', 'paiement_mode' => ModePaiement::Virement->value, 'paiement_reference' => 'DIR-DDC-NUT-EVAL',
                    'paiement_objet' => 'Paiement direct au cabinet d\'évaluation', 'paiement_description' => 'La DDC a payé directement le cabinet suisse d\'évaluation d\'impact.',
                    'id_enregistreur_paiement' => $daf->id_utilisateur,
                ]);
            }
        }

        if ($convAfdNut && $aTraore) {
            // AFD - Rubrique 1 : Plaidoyer (35M)
            $this->creerDemandeParcourue(
                convention: $convAfdNut, rubriqueLibelle: 'Plaidoyer', porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 35_000_000, objet: 'Ateliers de plaidoyer pour les cantines', description: 'Rencontres avec les autorités locales, maires et députés.',
                justificatif: 'justificatifs/nut-plaidoyer.pdf', rapport: 'rapports/nut-plaidoyer-rapport.pdf',
                status: StatutDemande::Terminee, dateCreation: '2022-11-05',
                paiement: ['montant' => 35_000_000, 'date' => '2022-12-01', 'mode' => ModePaiement::Virement, 'ref' => 'VIR-AFD-NUT-001']
            );
            // AFD - Rubrique 2 : Campagnes radio (25M)
            $this->creerDemandeParcourue(
                convention: $convAfdNut, rubriqueLibelle: 'Campagnes radio', porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 25_000_000, objet: 'Diffusion d\'émissions radio sur la nutrition', description: 'Contrats avec 15 radios communautaires pour 6 mois de diffusion.',
                justificatif: 'justificatifs/nut-radio.pdf', rapport: 'rapports/nut-radio-rapport.pdf',
                status: StatutDemande::Terminee, dateCreation: '2023-01-20',
                paiement: ['montant' => 25_000_000, 'date' => '2023-02-15', 'mode' => ModePaiement::Virement, 'ref' => 'VIR-AFD-NUT-002']
            );
            // AFD - Rubrique 3 : Enquêtes (12M)
            $this->creerDemandeParcourue(
                convention: $convAfdNut, rubriqueLibelle: 'Enquêtes', porteur: $aTraore, daf: $daf, ac: $ac,
                montant: 12_000_000, objet: 'Enquête nutritionnelle de base', description: 'Collecte de données anthropométriques des enfants.',
                justificatif: 'justificatifs/nut-enquete.pdf', rapport: 'rapports/nut-enquete-rapport.pdf',
                status: StatutDemande::Terminee, dateCreation: '2022-10-01',
                paiement: ['montant' => 12_000_000, 'date' => '2022-10-25', 'mode' => ModePaiement::Cheque, 'ref' => 'CHQ-AFD-NUT-003']
            );
            // AFD - Rubrique 4 : Capitalisation (8M) -> Paiement direct
            $rubCap = Rubrique::query()->where('id_convention', $convAfdNut->id_convention)->where('rubrique_libelle', 'like', '%Capitalisation%')->first();
            if ($rubCap) {
                Paiement::create([
                    'type_paiement' => 'direct', 'id_convention' => $convAfdNut->id_convention, 'id_projet' => $convAfdNut->id_projet, 'id_rubrique' => $rubCap->id_rubrique,
                    'paiement_montant' => 8_000_000, 'paiement_date' => '2024-11-20', 'paiement_mode' => ModePaiement::Virement->value, 'paiement_reference' => 'DIR-AFD-NUT-CAP',
                    'paiement_objet' => 'Impression et diffusion du livret de capitalisation', 'paiement_description' => 'Paiement direct de l\'imprimeur par l\'AFD.',
                    'id_enregistreur_paiement' => $daf->id_utilisateur,
                ]);
            }
        }

        // ==== 2. PROJET EN COURS : EMPLOI-JEUNES (Différents statuts) ====
        $convBmEmploi = Convention::query()->where('convention_titre', 'like', '%BM-EMPLOI%')->first();
        $sSanogo = $porteurs['seydou.sanogo@gmail.com'] ?? null;

        if ($convBmEmploi && $sSanogo) {
            // Validée DAF : Équipement de 10 centres (30M / 60M)
            $rubEq = Rubrique::query()->where('id_convention', $convBmEmploi->id_convention)->where('rubrique_libelle', 'like', '%Équipement%')->first();
            if ($rubEq) {
                DemandeDepense::create([
                    'id_rubrique' => $rubEq->id_rubrique, 'id_convention' => $convBmEmploi->id_convention, 'id_porteur' => $sSanogo->id_utilisateur,
                    'demande_montant' => 30_000_000, 'demande_objet' => 'Achat d\'équipements pour les centres de Koudougou et Bobo',
                    'demande_description' => 'Machines-outils pour la menuiserie bois et métallique.', 'demande_justificatif' => 'justificatifs/emploi-equip.pdf',
                    'demande_statut' => StatutDemande::ValideeDaf, 'demande_date_validation_daf' => now()->subDays(2), 'id_validateur_daf' => $daf->id_utilisateur,
                    'cree_le' => now()->subDays(5),
                ]);
            }

            // Rejetée DAF (avec motif) : Conception parcours (15M / 40M)
            $rubParcours = Rubrique::query()->where('id_convention', $convBmEmploi->id_convention)->where('rubrique_libelle', 'like', '%parcours%')->first();
            if ($rubParcours) {
                DemandeDepense::create([
                    'id_rubrique' => $rubParcours->id_rubrique, 'id_convention' => $convBmEmploi->id_convention, 'id_porteur' => $sSanogo->id_utilisateur,
                    'demande_montant' => 15_000_000, 'demande_objet' => 'Ateliers d\'ingénierie pédagogique',
                    'demande_description' => 'Élaboration de 5 parcours certifiants en énergie renouvelable.', 'demande_justificatif' => 'justificatifs/emploi-parcours.pdf',
                    'demande_statut' => StatutDemande::RejeteeDaf, 'demande_motif_rejet' => 'Le devis du consultant expert est expiré. Veuillez joindre un devis actualisé datant de moins de 3 mois.',
                    'cree_le' => now()->subDays(10), 'mis_a_jour_le' => now()->subDays(8),
                ]);
            }

            // Payée (Rapport Rejeté) : Suivi-insertion (10M / 20M)
            $rubSuivi = Rubrique::query()->where('id_convention', $convBmEmploi->id_convention)->where('rubrique_libelle', 'like', '%Suivi-insertion%')->first();
            if ($rubSuivi) {
                $d = DemandeDepense::create([
                    'id_rubrique' => $rubSuivi->id_rubrique, 'id_convention' => $convBmEmploi->id_convention, 'id_porteur' => $sSanogo->id_utilisateur,
                    'demande_montant' => 10_000_000, 'demande_objet' => 'Déploiement de l\'application de suivi des diplômés',
                    'demande_description' => 'Développement d\'une plateforme web pour tracer l\'insertion des 5000 jeunes formés.', 'demande_justificatif' => 'justificatifs/emploi-suivi.pdf',
                    'demande_rapport' => 'rapports/emploi-suivi-rapport.pdf',
                    'demande_statut' => StatutDemande::Payee, // Reste Payée si rapport rejeté
                    'demande_rapport_valide_daf' => false, 'demande_rapport_motif_rejet' => 'Le rapport d\'exécution manque de captures d\'écran de la plateforme fonctionnelle et la liste des modules livrés.',
                    'demande_date_validation_daf' => now()->subDays(40), 'id_validateur_daf' => $daf->id_utilisateur,
                    'demande_date_validation_ac' => now()->subDays(35), 'id_validateur_ac' => $ac->id_utilisateur,
                    'cree_le' => now()->subDays(45),
                ]);
                Paiement::create([
                    'id_demande' => $d->id_demande, 'id_projet' => $convBmEmploi->id_projet, 'id_convention' => $convBmEmploi->id_convention, 'id_rubrique' => $rubSuivi->id_rubrique,
                    'paiement_montant' => 10_000_000, 'paiement_date' => now()->subDays(30), 'paiement_mode' => ModePaiement::Virement->value, 'paiement_reference' => 'VIR-BM-EMPLOI-001',
                    'id_enregistreur_paiement' => $ac->id_utilisateur, 'type_paiement' => 'normal', 'paiement_objet' => 'Déploiement de l\'application',
                ]);
            }

            // Payée (Rapport Soumis, en attente de validation) : Fonds d'amorçage (20M / 30M)
            $rubFonds = Rubrique::query()->where('id_convention', $convBmEmploi->id_convention)->where('rubrique_libelle', 'like', '%Fonds%')->first();
            if ($rubFonds) {
                $this->creerDemandeParcourue(
                    convention: $convBmEmploi, rubriqueLibelle: null, porteur: $sSanogo, daf: $daf, ac: $ac,
                    montant: 20_000_000, objet: 'Subvention de démarrage pour 40 micro-entreprises', description: 'Octroi de micro-crédits de 500 000 FCFA à 40 jeunes diplômés.',
                    justificatif: 'justificatifs/emploi-fonds.pdf', rapport: 'rapports/emploi-fonds-rapport.pdf',
                    status: StatutDemande::RapportSoumis, dateCreation: now()->subDays(60)->toDateString(),
                    paiement: ['montant' => 20_000_000, 'date' => now()->subDays(45)->toDateString(), 'mode' => ModePaiement::Virement, 'ref' => 'VIR-BM-EMPLOI-002'],
                    rubrique: $rubFonds
                );
            }
        }

        // ==== 3. PROJET EN COURS : AGRO-TRANSF ====
        $convBadAgro = Convention::query()->where('convention_titre', 'like', '%BAD-AGRO%')->first();
        $hDiallo = $porteurs['halimatou.diallo@gmail.com'] ?? null;

        if ($convBadAgro && $hDiallo) {
            // Payée : Équipements (60M / 80M)
            $this->creerDemandeParcourue(
                convention: $convBadAgro, rubriqueLibelle: 'équipements de transformation', porteur: $hDiallo, daf: $daf, ac: $ac,
                montant: 60_000_000, objet: 'Acquisition de 5 séchoirs industriels pour mangues', description: 'Achat de 5 séchoirs à gaz de grande capacité pour les coopératives de Orodara.',
                justificatif: 'justificatifs/agro-sechoirs.pdf', rapport: null,
                status: StatutDemande::Payee, dateCreation: now()->subDays(90)->toDateString(),
                paiement: ['montant' => 60_000_000, 'date' => now()->subDays(80)->toDateString(), 'mode' => ModePaiement::Virement, 'ref' => 'VIR-BAD-AGRO-001']
            );

            // Validée AC : Certification (15M / 40M)
            $rubCert = Rubrique::query()->where('id_convention', $convBadAgro->id_convention)->where('rubrique_libelle', 'like', '%Certification%')->first();
            if ($rubCert) {
                DemandeDepense::create([
                    'id_rubrique' => $rubCert->id_rubrique, 'id_convention' => $convBadAgro->id_convention, 'id_porteur' => $hDiallo->id_utilisateur,
                    'demande_montant' => 15_000_000, 'demande_objet' => 'Audit de certification ISO 22000',
                    'demande_description' => 'Frais d\'intervention du cabinet certificateur ECOCERT pour 3 unités pilotes.', 'demande_justificatif' => 'justificatifs/agro-certif.pdf',
                    'demande_statut' => StatutDemande::ValideeAgentComptable, 'demande_date_validation_daf' => now()->subDays(15), 'id_validateur_daf' => $daf->id_utilisateur,
                    'demande_date_validation_ac' => now()->subDays(10), 'id_validateur_ac' => $ac->id_utilisateur, 'cree_le' => now()->subDays(20),
                ]);
            }

            // Soumise : HACCP (20M / 60M)
            $rubHaccp = Rubrique::query()->where('id_convention', $convBadAgro->id_convention)->where('rubrique_libelle', 'like', '%HACCP%')->first();
            if ($rubHaccp) {
                DemandeDepense::create([
                    'id_rubrique' => $rubHaccp->id_rubrique, 'id_convention' => $convBadAgro->id_convention, 'id_porteur' => $hDiallo->id_utilisateur,
                    'demande_montant' => 20_000_000, 'demande_objet' => 'Formation du personnel aux normes HACCP',
                    'demande_description' => 'Séminaires de formation pour les responsables qualité des 15 unités de transformation.', 'demande_justificatif' => 'justificatifs/agro-haccp.pdf',
                    'demande_statut' => StatutDemande::Soumise, 'cree_le' => now()->subDays(2),
                ]);
            }
        }

        // ==== 4. PROJET SUSPENDU : GOUV-MINES ====
        $convBmGouv = Convention::query()->where('convention_titre', 'like', '%BM-GOUV%')->first();
        $rOuedraogo = $porteurs['r.ouedraogo@ujkz.bf'] ?? null;

        if ($convBmGouv && $rOuedraogo) {
            // Terminée : Cartographie (30M)
            $this->creerDemandeParcourue(
                convention: $convBmGouv, rubriqueLibelle: 'Cartographie', porteur: $rOuedraogo, daf: $daf, ac: $ac,
                montant: 30_000_000, objet: 'Mission de recensement des sites d\'orpaillage au Nord', description: 'Géoréférencement de 120 sites avant la suspension du projet.',
                justificatif: 'justificatifs/gouv-carto.pdf', rapport: 'rapports/gouv-carto-rapport.pdf',
                status: StatutDemande::Terminee, dateCreation: '2024-11-10',
                paiement: ['montant' => 30_000_000, 'date' => '2024-12-05', 'mode' => ModePaiement::Virement, 'ref' => 'VIR-BM-GOUV-001']
            );
        }

        // ==== 5. PROJET EN ATTENTE : ROUTES-RESIL (Juste un paiement direct pour l'étude) ====
        $convBadRoutes = Convention::query()->where('convention_titre', 'like', '%BAD-ROUTES%')->first();
        $aOuattara = $porteurs['aissata.ouattara@gmail.com'] ?? null;

        if ($convBadRoutes && $aOuattara) {
            $rubEtudes = Rubrique::query()->where('id_convention', $convBadRoutes->id_convention)->where('rubrique_libelle', 'like', '%Études%')->first();
            if ($rubEtudes) {
                Paiement::create([
                    'type_paiement' => 'direct', 'id_convention' => $convBadRoutes->id_convention, 'id_projet' => $convBadRoutes->id_projet, 'id_rubrique' => $rubEtudes->id_rubrique,
                    'paiement_montant' => 25_000_000, 'paiement_date' => now()->subDays(10)->toDateString(), 'paiement_mode' => ModePaiement::Virement->value, 'paiement_reference' => 'DIR-BAD-ROUTES-ETUDE',
                    'paiement_objet' => 'Étude de faisabilité technique', 'paiement_description' => 'Paiement direct du cabinet d\'ingénierie par la BAD avant le démarrage effectif des travaux.',
                    'id_enregistreur_paiement' => $daf->id_utilisateur,
                ]);
            }
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
            $rubrique = Rubrique::query()->where('id_convention', $convention->id_convention)
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
                'type_paiement' => 'normal',
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
            'justificatifs/nut-dev-aliments.pdf',
            'justificatifs/nut-formation-meres.pdf',
            'justificatifs/nut-magasins.pdf',
            'justificatifs/nut-plaidoyer.pdf',
            'justificatifs/nut-radio.pdf',
            'justificatifs/nut-enquete.pdf',
            'justificatifs/emploi-equip.pdf',
            'justificatifs/emploi-parcours.pdf',
            'justificatifs/emploi-suivi.pdf',
            'justificatifs/emploi-fonds.pdf',
            'justificatifs/agro-sechoirs.pdf',
            'justificatifs/agro-certif.pdf',
            'justificatifs/agro-haccp.pdf',
            'justificatifs/gouv-carto.pdf',
            'rapports/nut-dev-aliments-rapport.pdf',
            'rapports/nut-formation-meres-rapport.pdf',
            'rapports/nut-magasins-rapport.pdf',
            'rapports/nut-plaidoyer-rapport.pdf',
            'rapports/nut-radio-rapport.pdf',
            'rapports/nut-enquete-rapport.pdf',
            'rapports/emploi-suivi-rapport.pdf',
            'rapports/emploi-fonds-rapport.pdf',
            'rapports/gouv-carto-rapport.pdf',
        ];

        foreach ($paths as $path) {
            if (! Storage::disk('private')->exists($path)) {
                Storage::disk('private')->put($path, $minimalPdf);
            }
        }
    }
}
