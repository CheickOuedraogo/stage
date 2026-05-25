# Dictionnaire de données — CIFEU Gestion Projet

> Convention : préfixe métier en français, snake_case. Timestamps : `cree_le` / `mis_a_jour_le`.
> La table `notifications` conserve les colonnes Laravel framework (non modifiables).

---

## Utilisateur (`utilisateurs`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_utilisateur | Identifiant unique de l'utilisateur | Entier | PK, NOT NULL, AUTO_INCREMENT |
| utilisateur_nom | Nom complet de l'utilisateur | Chaîne (255) | NOT NULL |
| utilisateur_email | Adresse e-mail, identifiant de connexion | Chaîne (255) | NOT NULL, UNIQUE |
| utilisateur_mot_de_passe | Mot de passe haché (bcrypt) | Chaîne (255) | NOT NULL |
| utilisateur_role | Rôle : `admin`, `daf`, `ac`, `porteur` | Chaîne (20) | NOT NULL, défaut : `porteur` |
| utilisateur_actif | Compte actif ; désactiver sans supprimer | Booléen | NOT NULL, défaut : true |
| utilisateur_avatar_chemin | Chemin relatif du fichier avatar | Chaîne (255) | NULLABLE |
| utilisateur_telephone | Numéro de téléphone | Chaîne (20) | NULLABLE |
| email_verifie_le | Horodatage de vérification de l'e-mail | Timestamp | NULLABLE |
| jeton_souvenir | Jeton Laravel « se souvenir de moi » | Chaîne (100) | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Projet (`projets`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_projet | Identifiant unique du projet | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_porteur | Porteur du projet | Entier | FK → utilisateurs(id_utilisateur), NOT NULL |
| projet_titre | Titre officiel du projet | Chaîne (255) | NOT NULL |
| projet_description | Description générale | Texte | NULLABLE |
| projet_objectifs | Objectifs attendus | Texte | NULLABLE |
| projet_activites | Activités prévues | Texte | NULLABLE |
| projet_montant_estime | Budget total estimé en FCFA | Entier (BIGINT) | NOT NULL |
| projet_statut | Statut : `en_attente_financement`, `en_cours`, `suspendu`, `termine`, `annule` | Chaîne (50) | NOT NULL, défaut : `en_attente_financement` |
| statut_final | Résultat à la clôture : `succes`, `echec` | Chaîne (20) | NULLABLE |
| projet_date_debut | Date de démarrage effective | Date | NULLABLE |
| projet_date_fin_prevue | Date de fin prévisionnelle | Date | NULLABLE |
| projet_date_fin_reelle | Date de fin réelle (à la clôture) | Date | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Bailleur (`bailleurs`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_bailleur | Identifiant unique du bailleur | Entier | PK, NOT NULL, AUTO_INCREMENT |
| bailleur_nom | Dénomination officielle | Chaîne (255) | NOT NULL |
| bailleur_sigle | Sigle ou acronyme (ex. PNUD, AFD) | Chaîne (50) | NULLABLE |
| bailleur_type | Catégorie : multilatéral, bilatéral, fondation… | Chaîne (100) | NULLABLE |
| bailleur_pays | Pays d'origine ou de siège | Chaîne (100) | NULLABLE |
| bailleur_contact | Nom du contact principal | Chaîne (255) | NULLABLE |
| bailleur_email | Adresse e-mail du contact | Chaîne (255) | NULLABLE |
| bailleur_telephone | Téléphone du contact | Chaîne (50) | NULLABLE |
| bailleur_adresse | Adresse postale | Chaîne (255) | NULLABLE |
| bailleur_description | Notes libres | Texte | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Convention (`conventions`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_convention | Identifiant unique de la convention | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_projet | Projet rattaché | Entier | FK → projets(id_projet), NOT NULL |
| id_bailleur | Bailleur signataire | Entier | FK → bailleurs(id_bailleur), NOT NULL |
| convention_titre | Intitulé officiel | Chaîne (255) | NOT NULL |
| convention_description | Résumé descriptif | Texte | NULLABLE |
| convention_montant | Montant dans la devise d'origine | Entier (BIGINT) | NOT NULL |
| convention_forme | Forme : `don`, `pret` | Chaîne (20) | NOT NULL, défaut : `don` |
| convention_devise | Code ISO-4217 de la devise | Chaîne (10) | NOT NULL, défaut : `XOF` |
| convention_taux_conversion | Taux de conversion vers FCFA | Décimal (12,6) | NOT NULL, défaut : 1 |
| convention_statut | Statut : `active`, `suspendue`, `terminee`, `annulee` | Chaîne (20) | NOT NULL, défaut : `active` |
| convention_date_signature | Date de signature | Date | NULLABLE |
| convention_date_debut | Date de démarrage | Date | NULLABLE |
| convention_date_fin | Date d'échéance | Date | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Rubrique (`rubriques`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_rubrique | Identifiant unique de la rubrique | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_convention | Convention de rattachement | Entier | FK → conventions(id_convention), NOT NULL |
| rubrique_libelle | Intitulé de la ligne budgétaire | Chaîne (255) | NOT NULL |
| rubrique_montant | Montant alloué en FCFA | Entier (BIGINT) | NOT NULL |
| rubrique_description | Description complémentaire | Texte | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Versement (`versements`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_versement | Identifiant unique du versement | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_convention | Convention concernée | Entier | FK → conventions(id_convention), NOT NULL |
| versement_montant | Montant reçu en FCFA | Entier (BIGINT) | NOT NULL |
| versement_type | Nature : `avance`, `tranche` | Chaîne (20) | NOT NULL, défaut : `tranche` |
| versement_reference | Référence bancaire | Chaîne (255) | NULLABLE |
| versement_description | Observations | Texte | NULLABLE |
| versement_date_reception | Date effective de réception | Date | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## DemandeDepense (`demandes_depense`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_demande | Identifiant unique de la demande | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_rubrique | Rubrique budgétaire imputée | Entier | FK → rubriques(id_rubrique), NOT NULL, CASCADE |
| id_convention | Convention de rattachement | Entier | FK → conventions(id_convention), NOT NULL, CASCADE |
| id_porteur | Porteur soumettant la demande | Entier | FK → utilisateurs(id_utilisateur), NOT NULL, CASCADE |
| demande_montant | Montant demandé en FCFA | Entier | NOT NULL |
| demande_objet | Objet synthétique de la dépense | Chaîne (255) | NOT NULL |
| demande_description | Justification détaillée | Texte | NULLABLE |
| demande_justificatif | Chemin du fichier justificatif (stockage privé) | Chaîne (255) | NULLABLE |
| demande_statut | Statut dans le circuit : `soumise`, `validee_daf`, `rejetee_daf`, `validee_ac`, `rejetee_ac`, `payee`, `rapport_soumis`, `terminee` | Chaîne (30) | NOT NULL, défaut : `soumise` |
| demande_motif_rejet | Motif renseigné en cas de rejet | Texte | NULLABLE |
| demande_rapport | Chemin du rapport d'exécution (stockage privé) | Chaîne (255) | NULLABLE |
| demande_date_validation_daf | Horodatage de la validation DAF | Timestamp | NULLABLE |
| id_validateur_daf | Agent DAF validateur | Entier | FK → utilisateurs(id_utilisateur), NULLABLE, SET NULL |
| demande_date_validation_ac | Horodatage de la validation AC | Timestamp | NULLABLE |
| id_validateur_ac | Agent Comptable validateur | Entier | FK → utilisateurs(id_utilisateur), NULLABLE, SET NULL |
| demande_rapport_valide_daf | Rapport validé par la DAF | Booléen | NOT NULL, défaut : false |
| demande_rapport_valide_ac | Rapport validé par l'Agent Comptable | Booléen | NOT NULL, défaut : false |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Paiement (`paiements`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_paiement | Identifiant unique du paiement | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_demande | Demande acquittée | Entier | FK → demandes_depense(id_demande), NOT NULL, UNIQUE, CASCADE |
| paiement_montant | Montant payé en FCFA | Entier | NOT NULL |
| paiement_mode | Mode : `virement`, `cheque`, `especes` | Chaîne (20) | NOT NULL |
| paiement_reference | Référence bancaire | Chaîne (255) | NULLABLE |
| paiement_date | Date d'exécution | Date | NULLABLE |
| id_enregistreur_paiement | Agent Comptable enregistreur | Entier | FK → utilisateurs(id_utilisateur), NOT NULL, CASCADE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## PaiementDirect (`paiements_directs`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_paiement_direct | Identifiant unique | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_convention | Convention imputée | Entier | FK → conventions(id_convention), NOT NULL, CASCADE |
| id_rubrique | Rubrique concernée (facultatif) | Entier | FK → rubriques(id_rubrique), NULLABLE, SET NULL |
| paiement_direct_montant | Montant en FCFA | Entier | NOT NULL |
| paiement_direct_objet | Objet du paiement | Chaîne (255) | NOT NULL |
| paiement_direct_description | Description complémentaire | Texte | NULLABLE |
| paiement_direct_date | Date d'exécution | Date | NULLABLE |
| id_enregistreur_paiement_direct | Agent DAF enregistreur | Entier | FK → utilisateurs(id_utilisateur), NOT NULL, CASCADE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Faq (`faq`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_faq | Identifiant unique de l'entrée FAQ | Entier | PK, NOT NULL, AUTO_INCREMENT |
| faq_question | Intitulé de la question fréquemment posée | Chaîne (255) | NOT NULL |
| faq_reponse | Réponse détaillée (format Markdown) | Texte | NOT NULL |
| faq_actif | Entrée visible ; masquer sans supprimer | Booléen | NOT NULL, défaut : true |
| visible_porteur | Visible pour les porteurs de projet | Booléen | NOT NULL, défaut : true |
| visible_daf | Visible pour les agents DAF | Booléen | NOT NULL, défaut : true |
| visible_ac | Visible pour les Agents Comptables | Booléen | NOT NULL, défaut : true |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de la dernière modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## MessageChat (`messages_chat`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_message | Identifiant unique du message | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_expediteur | Utilisateur expéditeur | Entier | FK → utilisateurs(id_utilisateur), NOT NULL, CASCADE |
| id_destinataire | Utilisateur destinataire | Entier | FK → utilisateurs(id_utilisateur), NOT NULL, CASCADE |
| message_contenu | Texte du message | Texte | NOT NULL |
| message_lu | Message lu par le destinataire | Booléen | NOT NULL, défaut : false |
| cree_le | Date et heure d'envoi | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## JournalAudit (`journaux_audit`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id_audit | Identifiant unique de l'entrée d'audit | Entier | PK, NOT NULL, AUTO_INCREMENT |
| id_utilisateur | Auteur de l'action (null si système) | Entier | FK → utilisateurs(id_utilisateur), NULLABLE, SET NULL |
| audit_action | Code de l'action (ex. `projet_cree`) | Chaîne (255) | NOT NULL |
| audit_entite_type | Classe PHP de l'entité concernée | Chaîne (255) | NULLABLE |
| audit_entite_id | Identifiant de l'entité | Entier (BIGINT) | NULLABLE |
| audit_anciennes_valeurs | Valeurs avant modification (JSON) | Texte | NULLABLE |
| audit_nouvelles_valeurs | Valeurs après modification (JSON) | Texte | NULLABLE |
| audit_adresse_ip | Adresse IP du client | Chaîne (45) | NULLABLE |
| audit_navigateur | User-Agent du navigateur | Texte | NULLABLE |
| audit_description | Description lisible de l'action | Chaîne (255) | NULLABLE |
| cree_le | Date et heure de l'action | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Parametre (`parametres`)

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id | Identifiant technique auto-incrémenté | Entier | PK, NOT NULL, AUTO_INCREMENT |
| parametre_cle | Clé unique (ex. `maintenance_mode`) | Chaîne (255) | NOT NULL, UNIQUE |
| parametre_valeur | Valeur texte libre | Texte | NULLABLE |
| cree_le | Date et heure de création | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |
| mis_a_jour_le | Date et heure de modification | Timestamp | NOT NULL, défaut : CURRENT_TIMESTAMP |

---

## Notification (`notifications`) — table système Laravel

> ⚠️ Colonnes gérées par le framework Laravel (trait `Notifiable`). Non modifiables.

| Attribut | Description | Type | Contraintes |
|---|---|---|---|
| id | UUID unique de la notification | UUID | PK, NOT NULL |
| type | Classe PHP de notification | Chaîne (255) | NOT NULL |
| notifiable_type | Type du modèle destinataire (polymorphique) | Chaîne (255) | NOT NULL |
| notifiable_id | ID du modèle destinataire | Entier (BIGINT) | NOT NULL |
| data | Payload JSON de la notification | Texte | NOT NULL |
| read_at | Horodatage de lecture (null si non lue) | Timestamp | NULLABLE |
| created_at | Date de création | Timestamp | NULLABLE |
| updated_at | Date de modification | Timestamp | NULLABLE |

---

## Récapitulatif des clés étrangères

| Table source | Colonne FK | Table cible | PK cible | Comportement |
|---|---|---|---|---|
| projets | id_porteur | utilisateurs | id_utilisateur | RESTRICT |
| conventions | id_projet | projets | id_projet | RESTRICT |
| conventions | id_bailleur | bailleurs | id_bailleur | RESTRICT |
| rubriques | id_convention | conventions | id_convention | RESTRICT |
| versements | id_convention | conventions | id_convention | RESTRICT |
| demandes_depense | id_rubrique | rubriques | id_rubrique | CASCADE |
| demandes_depense | id_convention | conventions | id_convention | CASCADE |
| demandes_depense | id_porteur | utilisateurs | id_utilisateur | CASCADE |
| demandes_depense | id_validateur_daf | utilisateurs | id_utilisateur | SET NULL |
| demandes_depense | id_validateur_ac | utilisateurs | id_utilisateur | SET NULL |
| paiements | id_demande | demandes_depense | id_demande | CASCADE |
| paiements | id_enregistreur_paiement | utilisateurs | id_utilisateur | CASCADE |
| paiements_directs | id_convention | conventions | id_convention | CASCADE |
| paiements_directs | id_rubrique | rubriques | id_rubrique | SET NULL |
| paiements_directs | id_enregistreur_paiement_direct | utilisateurs | id_utilisateur | CASCADE |
| messages_chat | id_expediteur | utilisateurs | id_utilisateur | CASCADE |
| messages_chat | id_destinataire | utilisateurs | id_utilisateur | CASCADE |
| journaux_audit | id_utilisateur | utilisateurs | id_utilisateur | SET NULL |
