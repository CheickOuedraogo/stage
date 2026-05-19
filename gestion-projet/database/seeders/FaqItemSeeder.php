<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'faq_question' => 'Comment soumettre une demande de dépense ?',
                'faq_reponse' => "Pour soumettre une demande de dépense :\n\n1. Accédez à **Mes Projets** dans la barre de navigation.\n2. Cliquez sur le projet concerné, puis sur la convention.\n3. Sur la page de la convention, cliquez sur **Nouvelle demande de dépense**.\n4. Remplissez le formulaire (rubrique, montant, objet, description) et joignez le justificatif PDF.\n5. Soumettez la demande.\n\n> **Attention** : vous ne pouvez avoir qu'une seule demande en cours par convention.",
                'faq_ordre' => 1,
            ],
            [
                'faq_question' => 'Quels formats de justificatifs sont acceptés ?',
                'faq_reponse' => "Seuls les fichiers au format **PDF** sont acceptés comme justificatifs de dépense.\n\nAssurez-vous que votre document :\n- Est lisible et complet\n- Ne dépasse pas la taille maximale autorisée\n- Contient toutes les informations nécessaires (facture, bon de commande, etc.)",
                'faq_ordre' => 2,
            ],
            [
                'faq_question' => 'Que faire si ma demande est rejetée ?',
                'faq_reponse' => "Si votre demande est rejetée par la DAF ou l'Agent Comptable :\n\n1. Consultez le **motif de rejet** dans les détails de la demande.\n2. Corrigez les éléments signalés (montant, rubrique, justificatif, etc.).\n3. Soumettez une **nouvelle demande** pour la même convention (la demande rejetée libère le verrou).\n\nSi vous avez des questions sur le motif de rejet, contactez la DAF.",
                'faq_ordre' => 3,
            ],
            [
                'faq_question' => 'Comment fonctionne le circuit de validation ?',
                'faq_reponse' => "Le circuit de validation des dépenses se déroule en plusieurs étapes :\n\n1. **Porteur** → Soumet la demande avec justificatif PDF\n2. **DAF** → Vérifie la conformité et la disponibilité budgétaire (valide ou rejette)\n3. **Agent Comptable** → Contrôle comptable (valide ou rejette)\n4. **Agent Comptable** → Enregistre le paiement\n5. **Porteur** → Uploade le rapport d'exécution\n6. **DAF + AC** → Valident le rapport → Demande terminée",
                'faq_ordre' => 4,
            ],
            [
                'faq_question' => 'Comment enregistrer un paiement direct du bailleur ?',
                'faq_reponse' => "Pour enregistrer un paiement direct effectué par le bailleur :\n\n1. Accédez à la page **Détail de la convention** concernée.\n2. Faites défiler jusqu'à la section **Paiements directs**.\n3. Cliquez sur **Nouveau paiement direct**.\n4. Renseignez le montant, la description, l'objet et la date.\n\n> Les paiements directs sont enregistrés sans passer par le circuit DAF/AC.",
                'faq_ordre' => 5,
            ],
            [
                'faq_question' => 'Comment consulter mes fonds disponibles ?',
                'faq_reponse' => "Vous pouvez consulter vos fonds disponibles à plusieurs niveaux :\n\n- **Par projet** : accédez à la page détail d'un projet pour voir les statistiques globales (versements, dépenses, disponible).\n- **Par convention** : la page de détail d'une convention affiche les stats de versements et de consommation.\n- **Par rubrique** : le tableau des rubriques montre le prévu, le consommé et le disponible pour chaque ligne budgétaire.",
                'faq_ordre' => 6,
            ],
            [
                'faq_question' => 'Que signifient les différents statuts d\'une demande ?',
                'faq_reponse' => "Les statuts possibles d'une demande de dépense :\n\n| Statut | Signification |\n|---|---|\n| **Soumise** | En attente de validation par la DAF |\n| **Validée DAF** | Approuvée par la DAF, en attente de l'AC |\n| **Validée AC** | Approuvée par l'AC |\n| **Payée** | Paiement enregistré, en attente de votre rapport |\n| **Rapport soumis** | Rapport uploadé, en attente de validation finale |\n| **Terminée** | Demande complètement clôturée |\n| **Rejetée DAF/AC** | Rejetée (voir le motif dans les détails) |",
                'faq_ordre' => 7,
            ],
            [
                'faq_question' => 'Comment uploader mon rapport d\'exécution après paiement ?',
                'faq_reponse' => "Après l'enregistrement du paiement par l'Agent Comptable :\n\n1. Accédez à **Mes Demandes** dans la barre de navigation.\n2. Cliquez sur la demande concernée (statut : **Payée**).\n3. Dans la section dédiée, cliquez sur **Uploader le rapport**.\n4. Sélectionnez votre fichier PDF.\n\nLe rapport sera ensuite validé par la DAF et l'AC pour clôturer définitivement la demande.",
                'faq_ordre' => 8,
            ],
        ];

        foreach ($items as $item) {
            FaqItem::create($item);
        }
    }
}
