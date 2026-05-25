# Dictionnaire de Données - CIFEU

Ce document répertorie toutes les entités de la base de données après la migration vers le Français Total.

## 1. Utilisateur (utilisateurs)
Représente les acteurs du système (Administrateur, DAF, AC, Porteur).

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_utilisateur | Identifiant unique de l'utilisateur | Entier | PK, NOT NULL |
| utilisateur_nom | Nom complet de l'utilisateur | Chaîne (255) | NOT NULL |
| utilisateur_email | Adresse e-mail servant d'identifiant | Chaîne (255) | NOT NULL, UNIQUE |
| utilisateur_mot_de_passe | Mot de passe haché | Chaîne | NOT NULL |
| utilisateur_role | Rôle (admin, daf, ac, porteur) | Chaîne | NOT NULL |
| utilisateur_actif | État du compte (actif/inactif) | Booléen | NOT NULL, défaut: true |
| utilisateur_avatar_chemin | Chemin vers le fichier image de l'avatar | Chaîne | NULLABLE |
| utilisateur_telephone | Numéro de téléphone | Chaîne (20) | NULLABLE |
| email_verifie_le | Date de vérification de l'e-mail | Timestamp | NULLABLE |
| jeton_souvenir | Jeton pour "se souvenir de moi" | Chaîne | NULLABLE |
| cree_le | Date de création du compte | Timestamp | NOT NULL |
| mis_a_jour_le | Date de dernière modification | Timestamp | NOT NULL |

## 2. Faq (faq)
Questions fréquemment posées pour l'aide aux utilisateurs.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_faq | Identifiant unique de l'entrée FAQ | Entier | PK, NOT NULL |
| faq_question | Intitulé de la question | Chaîne (255) | NOT NULL |
| faq_reponse | Réponse détaillée (format Markdown) | Texte | NOT NULL |
| faq_actif | Visibilité globale de la question | Booléen | NOT NULL, défaut: true |
| visible_porteur | Visible pour les Porteurs | Booléen | NOT NULL, défaut: true |
| visible_daf | Visible pour la DAF | Booléen | NOT NULL, défaut: true |
| visible_ac | Visible pour les Agents Comptables | Booléen | NOT NULL, défaut: true |
| cree_le | Date de création | Timestamp | NOT NULL |
| mis_a_jour_le | Date de dernière modification | Timestamp | NOT NULL |

## 3. Bailleur (bailleurs)
Organismes finançant les projets.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_bailleur | Identifiant unique du bailleur | Entier | PK, NOT NULL |
| bailleur_nom | Nom complet de l'organisme | Chaîne (255) | NOT NULL |
| bailleur_sigle | Sigle ou abréviation | Chaîne (50) | NULLABLE |
| bailleur_type | Type (multilatéral, bilatéral, etc.) | Chaîne | NULLABLE |
| bailleur_pays | Pays d'origine | Chaîne | NULLABLE |
| bailleur_contact | Nom de la personne contact | Chaîne | NULLABLE |
| bailleur_email | E-mail de contact | Chaîne | NULLABLE |
| bailleur_telephone | Téléphone de contact | Chaîne | NULLABLE |
| bailleur_adresse | Adresse physique | Texte | NULLABLE |
| bailleur_description | Description libre | Texte | NULLABLE |
| cree_le | Date de création | Timestamp | NOT NULL |
| mis_a_jour_le | Date de dernière modification | Timestamp | NOT NULL |

## 4. Projet (projets)
Entité principale représentant un projet de recherche ou développement.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_projet | Identifiant unique du projet | Entier | PK, NOT NULL |
| id_porteur | Référence au porteur responsable | Entier | FK (utilisateurs), NOT NULL |
| projet_titre | Titre du projet | Chaîne (255) | NOT NULL |
| projet_description | Résumé du projet | Texte | NULLABLE |
| projet_objectifs | Objectifs visés | Texte | NULLABLE |
| projet_activites | Activités prévues | Texte | NULLABLE |
| projet_montant_estime | Budget global estimé | Entier | NOT NULL |
| projet_statut | État d'avancement | Chaîne | NOT NULL |
| statut_final | Résultat à la clôture | Chaîne | NULLABLE |
| projet_date_debut | Date de début effective | Date | NULLABLE |
| projet_date_fin_prevue | Date de fin prévisionnelle | Date | NULLABLE |
| projet_date_fin_reelle | Date de clôture effective | Date | NULLABLE |
| cree_le | Date de création | Timestamp | NOT NULL |
| mis_a_jour_le | Date de dernière modification | Timestamp | NOT NULL |

## 5. Convention (conventions)
Accord contractuel liant un projet à un bailleur.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_convention | Identifiant unique | Entier | PK, NOT NULL |
| id_projet | Projet concerné | Entier | FK (projets), NOT NULL |
| id_bailleur | Bailleur financeur | Entier | FK (bailleurs), NOT NULL |
| convention_titre | Titre de la convention | Chaîne | NOT NULL |
| convention_description | Détails du contrat | Texte | NULLABLE |
| convention_montant | Montant engagé dans la devise d'origine | Entier | NOT NULL |
| convention_forme | Forme (Don, Prêt, etc.) | Chaîne | NOT NULL |
| convention_devise | Devise (XOF, EUR, USD, etc.) | Chaîne (3) | NOT NULL |
| convention_taux_conversion | Taux vers le FCFA | Décimal | NOT NULL, défaut: 1 |
| convention_statut | État (active, suspendue, etc.) | Chaîne | NOT NULL |
| convention_date_signature | Date de signature | Date | NULLABLE |
| convention_date_debut | Date d'entrée en vigueur | Date | NULLABLE |
| convention_date_fin | Date d'échéance | Date | NULLABLE |
| cree_le | Date d'enregistrement | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 6. Rubrique (rubriques)
Ligne budgétaire d'une convention.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_rubrique | Identifiant unique | Entier | PK, NOT NULL |
| id_convention | Convention parente | Entier | FK (conventions), NOT NULL |
| rubrique_libelle | Nom de la dépense autorisée | Chaîne | NOT NULL |
| rubrique_montant | Montant alloué à cette ligne | Entier | NOT NULL |
| rubrique_description | Détails sur l'utilisation | Texte | NULLABLE |
| cree_le | Date de création | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 7. Versement (versements)
Fonds reçus du bailleur pour une convention.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_versement | Identifiant unique | Entier | PK, NOT NULL |
| id_convention | Convention bénéficiaire | Entier | FK (conventions), NOT NULL |
| versement_montant | Montant reçu | Entier | NOT NULL |
| versement_date_reception | Date de réception effective | Date | NOT NULL |
| versement_type | Type (Avance, Tranche, Solde) | Chaîne | NOT NULL |
| versement_description | Notes sur le versement | Texte | NULLABLE |
| versement_reference | Référence bancaire ou pièce | Chaîne | NULLABLE |
| cree_le | Date de saisie | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 8. Demande de Dépense (demandes_depense)
Sollicitation de fonds par un porteur pour une rubrique.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_demande | Identifiant unique | Entier | PK, NOT NULL |
| id_rubrique | Ligne budgétaire concernée | Entier | FK (rubriques), NOT NULL |
| id_convention | Convention source | Entier | FK (conventions), NOT NULL |
| id_porteur | Porteur auteur de la demande | Entier | FK (utilisateurs), NOT NULL |
| demande_montant | Montant sollicité | Entier | NOT NULL |
| demande_objet | Objet succinct | Chaîne | NOT NULL |
| demande_description | Justification détaillée | Texte | NULLABLE |
| demande_justificatif | Chemin vers le PDF justificatif | Chaîne | NULLABLE |
| demande_statut | État du circuit (soumise, payée, etc.) | Chaîne | NOT NULL |
| demande_motif_rejet | Raison en cas de refus | Texte | NULLABLE |
| demande_rapport | Chemin vers le rapport d'exécution | Chaîne | NULLABLE |
| demande_date_validation_daf | Date validation DAF | Timestamp | NULLABLE |
| id_validateur_daf | Agent DAF ayant validé | Entier | FK (utilisateurs), NULLABLE |
| demande_date_validation_ac | Date validation AC | Timestamp | NULLABLE |
| id_validateur_ac | Agent AC ayant validé | Entier | FK (utilisateurs), NULLABLE |
| demande_rapport_valide_daf | Rapport approuvé DAF | Booléen | NOT NULL, défaut: false |
| demande_rapport_valide_ac | Rapport approuvé AC | Booléen | NOT NULL, défaut: false |
| cree_le | Date de soumission | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 9. Paiement (paiements)
Enregistrement comptable du décaissement.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_paiement | Identifiant unique | Entier | PK, NOT NULL |
| id_demande | Demande correspondante | Entier | FK (demandes_depense), UNIQUE |
| paiement_montant | Montant décaissé | Entier | NOT NULL |
| paiement_date | Date effective du paiement | Date | NOT NULL |
| paiement_mode | Mode (Virement, Chèque, etc.) | Chaîne | NOT NULL |
| paiement_reference | Référence du virement/chèque | Chaîne | NULLABLE |
| id_enregistreur_paiement | Agent AC ayant saisi | Entier | FK (utilisateurs), NOT NULL |
| cree_le | Date de saisie | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 10. Paiement Direct (paiements_directs)
Dépenses réglées directement par le bailleur sans passer par l'UJKZ.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_paiement_direct | Identifiant unique | Entier | PK, NOT NULL |
| id_convention | Convention concernée | Entier | FK (conventions), NOT NULL |
| id_rubrique | Rubrique concernée | Entier | FK (rubriques), NULLABLE |
| paiement_direct_montant | Montant réglé | Entier | NOT NULL |
| paiement_direct_objet | Objet de la dépense | Chaîne | NOT NULL |
| paiement_direct_description | Détails | Texte | NULLABLE |
| paiement_direct_date | Date du règlement | Date | NOT NULL |
| id_enregistreur_paiement_direct | Agent ayant saisi | Entier | FK (utilisateurs), NOT NULL |
| cree_le | Date de saisie | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 11. Journal d'Audit (journaux_audit)
Historique des actions critiques effectuées par les utilisateurs.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_audit | Identifiant unique | Entier | PK, NOT NULL |
| id_utilisateur | Auteur de l'action | Entier | FK (utilisateurs), NULLABLE |
| audit_action | Type d'action (création, modification, etc.) | Chaîne | NOT NULL |
| audit_entite_type | Classe du modèle impacté | Chaîne | NULLABLE |
| audit_entite_id | Identifiant de l'entité impactée | Entier | NULLABLE |
| audit_anciennes_valeurs | Valeurs avant modification | JSON | NULLABLE |
| audit_nouvelles_valeurs | Valeurs après modification | JSON | NULLABLE |
| audit_adresse_ip | IP de l'utilisateur | Chaîne | NULLABLE |
| audit_navigateur | User Agent | Texte | NULLABLE |
| audit_description | Description textuelle lisible | Chaîne | NULLABLE |
| cree_le | Date de l'action | Timestamp | NOT NULL |

## 12. Message de Chat (messages_chat)
Messagerie interne entre les utilisateurs et l'administration.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id_message | Identifiant unique | Entier | PK, NOT NULL |
| id_expediteur | Utilisateur envoyant | Entier | FK (utilisateurs), NOT NULL |
| id_destinataire | Utilisateur recevant | Entier | FK (utilisateurs), NOT NULL |
| message_contenu | Corps du message | Texte | NOT NULL |
| message_lu | État de lecture | Booléen | NOT NULL, défaut: false |
| cree_le | Date d'envoi | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |

## 13. Paramètre (parametres)
Configuration système dynamique.

| Attribut | Description | Type | Contraintes |
| :--- | :--- | :--- | :--- |
| id | Identifiant unique | Entier | PK, NOT NULL |
| parametre_cle | Clé technique (slug) | Chaîne | NOT NULL, UNIQUE |
| parametre_valeur | Valeur associée | Texte | NULLABLE |
| cree_le | Date de création | Timestamp | NOT NULL |
| mis_a_jour_le | Date de modification | Timestamp | NOT NULL |
