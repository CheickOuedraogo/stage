# 📋 PROJECT.md — Module de Gestion Financière CIFEU

> **Projet** : Circuit Intégré des Financements Extérieurs Universitaires (CIFEU)
> **Module** : Suivi de la mobilisation des ressources, gestion budgétaire et contrôle d'exécution des dépenses
> **Organisation** : Université Joseph KI-ZERBO — Direction des Systèmes d'Information (DSI)
> **Stack technique** : Laravel 13 + Inertia.js v3 + React + TypeScript
> **Méthodologie** : Scrum — Sprints de 2 semaines
> **Durée restante** : ~8 semaines (~6h/jour)
> **Date de début** : 19 avril 2026
> **Date de fin estimée** : 14 juin 2026

> ⚠️ **L'ensemble de l'interface utilisateur est en FRANÇAIS.**

---

## Table des matières

1. [Contexte et périmètre](#1-contexte-et-périmètre)
2. [Acteurs et rôles](#2-acteurs-et-rôles)
3. [Parcours utilisateurs détaillés (UI/UX)](#3-parcours-utilisateurs-détaillés-uiux)
4. [Décisions techniques](#4-décisions-techniques)
5. [Modèle de données](#5-modèle-de-données)
6. [Backlog produit](#6-backlog-produit)
7. [Découpage en sprints](#7-découpage-en-sprints)
8. [Stratégie de seeders](#8-stratégie-de-seeders)
9. [Perspectives futures](#9-perspectives-futures)

---

## 1. Contexte et périmètre

### 1.1 Ce que fait ce module

Ce module intervient **après la signature des conventions** de financement. Il gère :

- Le **suivi de la mobilisation des ressources** (versements des bailleurs)
- La **gestion des budgets par rubriques** (conformément aux conventions)
- Le **circuit de validation et d'exécution des dépenses** (porteur → DAF → AC)
- L'**enregistrement des paiements directs** par les bailleurs
- La **traçabilité** de toutes les opérations financières
- Le **reporting** et la **clôture de projet** avec analyse des écarts (temps + budget)
- Un **système d'assistance** : FAQ statique pour les porteurs, chat admin pour DAF/AC

### 1.2 Ce que ce module ne fait PAS

- ❌ Création/gestion des projets (module externe — données simulées via seeders détaillés)
- ❌ Création/gestion des conventions (module externe — données simulées via seeders détaillés)
- ❌ Gestion des bailleurs (module externe — présents dans le système via seeders)
- ❌ Envoi d'emails / notifications par mail (perspective future)
- ❌ Inscription publique (l'admin crée les comptes)
- ❌ Mot de passe oublié (perspective future)
- ❌ Connexion Google/SSO (perspective future)
- ❌ Conversion de devises via API externe (taux statiques uniquement)

### 1.3 Contraintes générales

| Contrainte | Détail |
|---|---|
| **Langue** | Interface 100% en français |
| **Devise** | Tout en FCFA, conversion statique si devise étrangère saisie |
| **Justificatifs** | Un seul fichier PDF par demande de dépense |
| **Notifications** | In-app uniquement (pas de mail) |
| **Authentification** | Indépendante, locale. Pas d'inscription publique |
| **Utilisateurs estimés** | ~200 (surtout des porteurs) |
| **DAF / AC** | Un seul compte par rôle |
| **Descriptions** | Les champs description (projets, conventions, etc.) acceptent le **Markdown** |
| **Demandes de dépense** | **Une seule demande en cours à la fois par convention** — tant qu'une demande n'est pas finalisée ou rejetée, le porteur ne peut pas en soumettre une nouvelle sur la même convention |

---

## 2. Acteurs et rôles

### 2.1 Administrateur (`admin`)
- Crée/modifie/désactive les comptes utilisateurs
- Attribue les rôles
- Active/désactive le mode maintenance (avec date de fin estimée)
- Consulte le journal d'audit (actions des utilisateurs)
- Répond aux messages des DAF/AC via le chat intégré
- **N'intervient PAS** dans la gestion financière

### 2.2 Porteur de projet (`porteur`)
- Gère son profil (photo, informations — sauf email)
- Consulte ses projets avec barre de recherche
- Consulte les statistiques par projet (fonds disponibles, utilisés, donnés par bailleur)
- Consulte les conventions d'un projet et leurs détails
- Soumet des demandes de dépense (une seule à la fois par convention)
- Enregistre les paiements directs des bailleurs
- Consulte une section **Assistance (FAQ)** avec des réponses statiques aux questions fréquentes

### 2.3 Direction de l'Administration et des Finances (`daf`)
- Crée les rubriques budgétaires à partir des conventions
- Enregistre les versements/fonds reçus des bailleurs
- Valide ou rejette les demandes de dépenses (1er niveau — conformité + budget)
- Consulte les projets individuels pour des statistiques détaillées
- Consulte les statistiques globales (tous les projets)
- Génère des rapports d'exécution
- Accède au **chat avec l'admin** pour assistance

### 2.4 Agent Comptable (`ac`)
- Valide ou rejette les demandes approuvées par la DAF (2ème niveau — aspect comptable)
- Enregistre les paiements effectués
- Consulte les tableaux de bord financiers
- Accède au **chat avec l'admin** pour assistance

### 2.5 Bailleur de fonds — Entité passive
- N'a pas de compte utilisateur
- Représenté comme entité de données (nom, type, pays, contact)
- Données simulées via seeders

---

## 3. Parcours utilisateurs détaillés (UI/UX)

### 3.1 Page de connexion

- Champ email + mot de passe
- Pas de lien « Mot de passe oublié » (perspective future)
- Pas de lien « S'inscrire » (l'admin crée les comptes)
- Après connexion → redirection vers le dashboard correspondant au rôle
- Si mode maintenance actif → message avec date de retour estimée (sauf admin)

---

### 3.2 Interface Porteur de projet

#### Navigation (sidebar/navbar)

```
📁 Mes Projets          ← page d'accueil du porteur
📝 Mes Demandes         ← liste de toutes les demandes (tous projets)
👤 Mon Profil           ← gestion profil
❓ Assistance           ← FAQ statique
🔔 Notifications       ← badge avec compteur
```

#### Page « Mes Projets »
- **Barre de recherche** pour filtrer les projets
- **Liste des projets** sous forme de cartes avec :
  - Titre du projet
  - Statut (badge coloré : en cours, en attente de financement, terminé, annulé, suspendu)
  - Montant estimé
  - % de financement mobilisé (barre de progression)

#### Page « Détail d'un projet » (au clic sur un projet)
- **En-tête** : Titre, statut, dates (début/fin prévue/réelle)
- **Description du projet** en Markdown rendu
- **Section statistiques** (en haut, bien visible) :
  - 💰 Montant total estimé du projet
  - 📥 Fonds reçus des bailleurs (total mobilisé) / Montant prévu
  - 💸 Fonds utilisés (dépenses exécutées) / Fonds reçus
  - 📊 Fonds disponibles restants
  - Visualisation graphique (barres ou cercles)
- **Liste des conventions** (en dessous des stats) :
  - Chaque convention affichée en carte avec : bailleur, montant, forme (prêt/don), statut
  - Au clic → page détail convention

#### Page « Détail d'une convention »
- Informations de la convention (bailleur, montant FCFA, forme, dates, description Markdown)
- **Statistiques de la convention** :
  - Montant prévu vs mobilisé vs dépensé vs disponible
  - Graphique visuel
- **Rubriques budgétaires** (tableau) :
  - Libellé | Montant prévu | Montant consommé | Solde disponible
- **Bouton « Nouvelle demande de dépense »** (grisé si une demande est déjà en cours)
- **Historique des demandes** liées à cette convention
- **Historique des versements** reçus
- **Historique des paiements directs**

#### Page « Mes Demandes » (navbar)
- Liste de toutes les demandes du porteur, tous projets confondus
- Filtres : par statut, par projet, par convention
- Pour chaque demande : projet, convention, rubrique, montant, statut (avec timeline visuelle), date
- Au clic → détail de la demande avec toutes les étapes

#### Page « Mon Profil »
- Photo de profil (upload/modification)
- Nom, prénom (modifiables)
- Email (affiché mais **non modifiable**)
- Bouton changer mot de passe

#### Page « Assistance »
- Page statique avec FAQ en accordéon
- Questions/réponses pré-rédigées couvrant :
  - Comment soumettre une demande de dépense ?
  - Quels formats de justificatifs sont acceptés ?
  - Que faire si ma demande est rejetée ?
  - Comment fonctionne le circuit de validation ?
  - Comment enregistrer un paiement direct ?
  - etc.

---

### 3.3 Interface DAF

#### Navigation

```
📊 Tableau de bord      ← statistiques globales
📁 Projets              ← tous les projets (pas seulement les siens)
📝 Demandes             ← demandes en attente + historique
💰 Versements           ← gestion des versements reçus
📋 Rubriques            ← gestion des rubriques budgétaires
📈 Rapports             ← génération de rapports
💬 Assistance           ← chat avec l'admin
🔔 Notifications
```

#### Tableau de bord DAF
- **Statistiques globales** :
  - Nombre de projets actifs / total
  - Budget total prévu vs consommé (tous projets)
  - Taux global d'exécution budgétaire
  - Nombre de demandes en attente de validation DAF
- **Graphiques** :
  - Répartition des fonds par projet (camembert)
  - Évolution des dépenses dans le temps (courbe)
  - Top rubriques les plus consommées
- **Liste rapide** des demandes en attente de validation

#### Page « Projets » (DAF)
- Vue sur **tous les projets** (pas seulement ceux d'un porteur)
- Barre de recherche + filtres par statut
- Au clic sur un projet → mêmes statistiques que le porteur + vue détaillée des conventions et rubriques

#### Page « Demandes » (DAF)
- **Onglet « En attente »** : demandes soumises nécessitant validation DAF
- **Onglet « Historique »** : toutes les demandes traitées
- Pour chaque demande : détails complets + boutons Valider / Rejeter (avec champ motif)

#### Page « Versements »
- Formulaire d'enregistrement : convention, montant, date, type (avance/tranche), référence
- Liste de tous les versements enregistrés avec filtres
- Possibilité de modifier/annuler un versement

#### Page « Rubriques »
- Création de rubriques pour une convention donnée
- Vue du récapitulatif : prévu vs consommé par rubrique
- Contrôle : la somme des rubriques ≤ montant de la convention

#### Page « Rapports »
- Sélection du projet / convention
- Choix du format : **PDF ou Excel**
- Types de rapports :
  - Rapport d'exécution budgétaire
  - Rapport de clôture de projet (avec écarts temps + budget)
  - Rapport de mobilisation des ressources

#### Page « Assistance » (Chat)
- Interface de chat en temps réel avec l'admin
- Historique des conversations
- Indicateur en ligne/hors ligne de l'admin

---

### 3.4 Interface Agent Comptable (AC)

#### Navigation

```
📊 Tableau de bord      ← demandes en attente + paiements récents
📝 Demandes             ← demandes validées DAF en attente
💳 Paiements            ← historique des paiements effectués
💬 Assistance           ← chat avec l'admin
🔔 Notifications
```

#### Tableau de bord AC
- Nombre de demandes en attente de validation AC
- Paiements récents effectués
- Montant total décaissé (période)

#### Page « Demandes » (AC)
- Liste des demandes **validées par la DAF** en attente de validation AC
- Pour chaque demande : détails complets + justificatif téléchargeable
- Boutons Valider / Rejeter (avec motif)
- À la validation → formulaire d'enregistrement du paiement (montant, date, mode, référence)

#### Page « Paiements »
- Historique de tous les paiements effectués
- Filtres par projet, convention, date, mode de paiement

#### Page « Assistance » (Chat)
- Même interface de chat que la DAF

---

### 3.5 Interface Administrateur

#### Navigation

```
📊 Tableau de bord      ← stats globales du système
👥 Utilisateurs         ← CRUD complet
🔧 Maintenance          ← mode maintenance
📜 Journal d'audit      ← toutes les actions
💬 Messages             ← conversations chat avec DAF/AC
```

#### Tableau de bord Admin
- Nombre total d'utilisateurs (actifs/inactifs par rôle)
- Nombre de projets dans le système
- Dernières actions (mini journal d'audit)
- Statut du mode maintenance

#### Page « Utilisateurs »
- Liste avec filtres (rôle, statut actif/inactif)
- Création : nom, email, rôle, mot de passe temporaire
- Modification : nom, email, rôle, réinitialisation mot de passe
- Activation / désactivation de compte

#### Page « Maintenance »
- Toggle on/off du mode maintenance
- Champ : raison de la maintenance
- Champ : date/heure de fin estimée
- Quand activé : tous les utilisateurs sont déconnectés
- Le mode se désactivate automatiquement à l'heure prévue

#### Page « Journal d'audit »
- Liste chronologique de toutes les actions
- Filtres : par utilisateur, par type d'action, par date
- Détail : qui, quoi, quand, anciennes/nouvelles valeurs

#### Page « Messages »
- Liste des conversations (DAF, AC)
- Interface chat pour répondre
- Indicateur de messages non lus

---

## 4. Décisions techniques

### 4.1 Architecture

| Composant | Choix | Justification |
|---|---|---|
| Backend | Laravel 13 (PHP 8.4) | Stack imposé par le projet |
| Frontend | React + TypeScript (Inertia.js v3) | SPA sans API, rendu côté serveur |
| Base de données | MySQL/PostgreSQL | À confirmer |
| Authentification | Locale, gérée par l'admin | Pas de SSO/LDAP |
| Routing frontend | Wayfinder | Génération typée des routes |
| Tests | Pest v4 | Convention du projet |
| Formatter | Pint | Convention du projet |
| Markdown | `react-markdown` (front) | Rendu des descriptions projet/convention |
| Chat | Laravel Broadcasting + Reverb ou polling | Temps réel DAF/AC ↔ Admin |
| Export PDF | DomPDF ou Snappy | Rapports PDF |
| Export Excel | Laravel Excel (Maatwebsite) | Rapports Excel |

### 4.2 Patterns appliqués

- **Form Requests** pour toute validation
- **Policies** pour l'autorisation par rôle
- **Observers / Events** pour le journal d'audit
- **Enums PHP** pour les statuts et types
- **Service classes** pour la logique métier complexe (calcul de soldes, contrôle budgétaire)
- **Middleware** custom pour le mode maintenance et la redirection par rôle

### 4.3 Mode maintenance

- L'admin active le mode maintenance avec **raison** + **date/heure de fin estimée**
- Tous les utilisateurs sont déconnectés immédiatement (sessions invalidées)
- L'écran de login affiche un message de maintenance avec la date de retour
- Le mode se désactive **automatiquement** à l'heure prévue (via scheduled command)
- Si l'admin se déconnecte accidentellement → il attend la fin prévue, puis peut se reconnecter
- L'admin peut réactiver le mode maintenance si nécessaire

---

## 5. Modèle de données

### 5.1 Diagramme des entités

```
┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
│      users       │       │     projets      │       │    bailleurs     │
│──────────────────│       │──────────────────│       │──────────────────│
│ id               │◄──┐   │ id               │       │ id               │
│ name             │   │   │ titre            │       │ nom              │
│ email (unique)   │   │   │ description (md) │       │ sigle            │
│ password         │   └───│ porteur_id (FK)  │       │ type             │
│ role (enum)      │       │ activites (md)   │       │ pays             │
│ is_active        │       │ objectifs (md)   │       │ contact          │
│ avatar_path      │       │ montant_estime   │       │ email            │
│ telephone        │       │ status (enum)    │       │ telephone        │
│ timestamps       │       │ date_debut       │       │ adresse          │
└──────────────────┘       │ date_fin_prevue  │       │ description      │
                           │ date_fin_reelle  │       │ timestamps       │
                           │ timestamps       │       └──────┬───────────┘
                           └────────┬─────────┘              │
                                    │                        │
                                    │ 1:N                    │
                                    ▼                        │
                           ┌──────────────────┐              │
                           │   conventions    │◄─────────────┘
                           │──────────────────│    N:1
                           │ id               │
                           │ projet_id (FK)   │
                           │ bailleur_id (FK) │
                           │ titre            │
                           │ description (md) │
                           │ montant          │
                           │ forme (enum)     │  ← prêt | don
                           │ devise_origine   │
                           │ taux_conversion  │
                           │ montant_fcfa     │  ← montant converti
                           │ status (enum)    │
                           │ date_signature   │
                           │ date_debut       │
                           │ date_fin         │
                           │ timestamps       │
                           └────────┬─────────┘
                                    │
                        ┌───────────┼───────────┐
                        │ 1:N       │ 1:N       │ 1:N
                        ▼           ▼           ▼
        ┌────────────────┐ ┌──────────────┐ ┌──────────────────────┐
        │   rubriques    │ │  versements  │ │  paiements_directs   │
        │────────────────│ │──────────────│ │──────────────────────│
        │ id             │ │ id           │ │ id                   │
        │ convention_id  │ │ convention_id│ │ convention_id (FK)   │
        │ libelle        │ │ montant      │ │ rubrique_id (FK|null)│
        │ montant_prevu  │ │ date_reception│ │ montant             │
        │ description    │ │ type (enum)  │ │ description          │
        │ timestamps     │ │ description  │ │ objet_depense        │
        └───────┬────────┘ │ reference    │ │ date_paiement        │
                │          │ timestamps   │ │ enregistre_par (FK)  │
                │ 1:N      └──────────────┘ │ timestamps           │
                ▼                           └──────────────────────┘
        ┌──────────────────┐
        │ demandes_depenses│
        │──────────────────│
        │ id               │
        │ rubrique_id (FK) │
        │ convention_id(FK)│  ← pour la contrainte "1 demande active par convention"
        │ porteur_id (FK)  │
        │ montant          │
        │ objet            │
        │ description      │
        │ justificatif_path│  ← PDF uniquement
        │ status (enum)    │  ← soumise → validee_daf → validee_ac → payee
        │ motif_rejet      │  ←            rejetee_daf | rejetee_ac
        │ rapport_path     │  ← PDF rapport uploadé après validation
        │ validee_daf_at   │
        │ validee_daf_par  │
        │ validee_ac_at    │
        │ validee_ac_par   │
        │ timestamps       │
        └───────┬──────────┘
                │ 1:1
                ▼
        ┌──────────────────┐
        │    paiements     │
        │──────────────────│
        │ id               │
        │ demande_id (FK)  │  ← unique
        │ montant          │
        │ date_paiement    │
        │ mode_paiement    │  ← virement | chèque | espèces
        │ reference        │
        │ enregistre_par   │
        │ timestamps       │
        └──────────────────┘

        ┌──────────────────┐         ┌──────────────────┐
        │   audit_logs     │         │  chat_messages   │
        │──────────────────│         │──────────────────│
        │ id               │         │ id               │
        │ user_id (FK)     │         │ sender_id (FK)   │
        │ action           │         │ receiver_id (FK) │  ← admin
        │ auditable_type   │         │ message          │
        │ auditable_id     │         │ is_read          │
        │ old_values (json)│         │ timestamps       │
        │ new_values (json)│         └──────────────────┘
        │ ip_address       │
        │ user_agent       │
        │ timestamp        │
        └──────────────────┘

        ┌──────────────────┐         ┌──────────────────┐
        │   notifications  │         │     settings     │
        │──────────────────│         │──────────────────│
        │ (Laravel built-in│         │ id               │
        │  notification    │         │ key              │
        │  system)         │         │ value            │
        └──────────────────┘         │ timestamps       │
                                     └──────────────────┘

        ┌──────────────────┐
        │  faq_items       │
        │──────────────────│
        │ id               │
        │ question         │
        │ reponse (md)     │
        │ ordre            │
        │ is_active        │
        │ timestamps       │
        └──────────────────┘
```

### 5.2 Enums

```php
// App\Enums\UserRole
enum UserRole: string {
    case Admin = 'admin';
    case Daf = 'daf';
    case Ac = 'ac';
    case Porteur = 'porteur';
}

// App\Enums\ProjectStatus
enum ProjectStatus: string {
    case EnAttenteFinancement = 'en_attente_financement';
    case EnCours = 'en_cours';
    case Suspendu = 'suspendu';
    case Termine = 'termine';
    case Annule = 'annule';
}

// App\Enums\ConventionStatus
enum ConventionStatus: string {
    case Active = 'active';
    case Suspendue = 'suspendue';
    case Terminee = 'terminee';
    case Annulee = 'annulee';
}

// App\Enums\ConventionForme
enum ConventionForme: string {
    case Pret = 'pret';
    case Don = 'don';
}

// App\Enums\VersementType
enum VersementType: string {
    case Avance = 'avance';
    case Tranche = 'tranche';
}

// App\Enums\DemandeStatus
enum DemandeStatus: string {
    case Soumise = 'soumise';
    case ValidéeDaf = 'validee_daf';
    case RejetéeDaf = 'rejetee_daf';
    case ValidéeAc = 'validee_ac';
    case RejetéeAc = 'rejetee_ac';
    case Payee = 'payee';
}

// App\Enums\ModePaiement
enum ModePaiement: string {
    case Virement = 'virement';
    case Cheque = 'cheque';
    case Especes = 'especes';
}
```

---

## 6. Backlog produit

**Légende des priorités :**
- 🔴 **P0** — Critique (MVP, sans ça le système ne fonctionne pas)
- 🟠 **P1** — Important (nécessaire pour une utilisation complète)
- 🟡 **P2** — Utile (améliore l'expérience mais pas bloquant)

---

### EPIC 1 : Authentification et gestion des utilisateurs

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-1.1 | En tant qu'**admin**, je veux me connecter avec email/mot de passe | 🔴 P0 | S1 |
| US-1.2 | En tant qu'**utilisateur**, je veux être redirigé vers mon dashboard après connexion (selon mon rôle) | 🔴 P0 | S1 |
| US-1.3 | En tant qu'**admin**, je veux créer des comptes utilisateurs avec un rôle (daf, ac, porteur) | 🔴 P0 | S1 |
| US-1.4 | En tant qu'**admin**, je veux activer/désactiver un compte utilisateur | 🔴 P0 | S1 |
| US-1.5 | En tant qu'**admin**, je veux modifier les infos d'un utilisateur (nom, email, rôle, réinitialiser mdp) | 🟠 P1 | S1 |
| US-1.6 | En tant qu'**admin**, je veux voir la liste des utilisateurs avec filtrage par rôle et statut | 🟠 P1 | S1 |
| US-1.7 | En tant que **porteur**, je veux modifier mon profil (nom, photo, téléphone) mais pas mon email | 🟠 P1 | S1 |
| US-1.8 | En tant qu'**utilisateur**, je veux changer mon mot de passe | 🟠 P1 | S1 |

---

### EPIC 2 : Administration système

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-2.1 | En tant qu'**admin**, je veux activer le mode maintenance avec raison et date/heure de fin | 🟠 P1 | S1 |
| US-2.2 | En tant que **système**, tous les utilisateurs sont déconnectés à l'activation du mode maintenance | 🟠 P1 | S1 |
| US-2.3 | En tant que **système**, le mode maintenance se désactive automatiquement à l'heure prévue | 🟠 P1 | S1 |
| US-2.4 | En tant qu'**admin**, je veux voir le journal d'audit de toutes les actions utilisateurs | 🟠 P1 | S2 |
| US-2.5 | En tant qu'**admin**, je veux un dashboard avec stats globales (utilisateurs, projets, état système) | 🟡 P2 | S4 |

---

### EPIC 3 : Données de base (Projets, Conventions, Bailleurs)

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-3.1 | En tant que **système**, les projets sont simulés via seeders super détaillés (descriptions Markdown, activités, objectifs, montants) | 🔴 P0 | S2 |
| US-3.2 | En tant que **système**, les conventions sont simulées avec lien projets ↔ bailleurs, montants, forme prêt/don | 🔴 P0 | S2 |
| US-3.3 | En tant que **système**, les bailleurs sont pré-enregistrés via seeders (nom, sigle, pays, contact) | 🔴 P0 | S2 |
| US-3.4 | En tant que **porteur**, je veux voir mes projets avec barre de recherche et filtres par statut | 🔴 P0 | S2 |
| US-3.5 | En tant que **porteur**, je veux voir les statistiques d'un projet (fonds reçus/utilisés/disponibles) | 🔴 P0 | S2 |
| US-3.6 | En tant que **porteur**, je veux voir les conventions d'un projet avec détails et stats | 🔴 P0 | S2 |
| US-3.7 | En tant que **daf**, je veux consulter n'importe quel projet avec statistiques détaillées | 🔴 P0 | S2 |
| US-3.8 | En tant que **daf**, je veux modifier le statut d'un projet ou d'une convention | 🟠 P1 | S2 |
| US-3.9 | En tant que **système**, les descriptions en Markdown sont rendues correctement à l'affichage | 🟠 P1 | S2 |
| US-3.10 | En tant qu'**utilisateur**, les montants en devise étrangère sont convertis en FCFA (taux statique) | 🟡 P2 | S2 |

---

### EPIC 4 : Gestion budgétaire

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-4.1 | En tant que **daf**, je veux créer des rubriques budgétaires pour une convention (libellé, montant prévu) | 🔴 P0 | S2 |
| US-4.2 | En tant que **daf**, je veux modifier ou supprimer une rubrique budgétaire | 🟠 P1 | S2 |
| US-4.3 | En tant que **daf/porteur**, je veux voir le récapitulatif budgétaire par rubrique (prévu vs consommé vs disponible) | 🔴 P0 | S3 |
| US-4.4 | En tant que **système**, une dépense ne peut pas dépasser le montant prévu d'une rubrique | 🔴 P0 | S3 |
| US-4.5 | En tant que **système**, la somme des rubriques ne peut pas dépasser le montant de la convention | 🔴 P0 | S2 |

---

### EPIC 5 : Suivi de la mobilisation des ressources

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-5.1 | En tant que **daf**, je veux enregistrer un versement reçu (convention, montant, date, type avance/tranche, référence) | 🔴 P0 | S2 |
| US-5.2 | En tant que **daf/porteur**, je veux voir l'historique des versements pour une convention | 🔴 P0 | S2 |
| US-5.3 | En tant que **daf/porteur**, je veux voir le montant mobilisé vs prévu d'une convention | 🔴 P0 | S2 |
| US-5.4 | En tant que **daf**, je veux modifier ou annuler un versement enregistré par erreur | 🟠 P1 | S3 |

---

### EPIC 6 : Circuit de validation des dépenses

> ⚠️ **Circuit de validation** : Porteur soumet → DAF valide/rejette → AC valide/rejette → AC enregistre paiement → Porteur upload rapport → DAF + AC valident le rapport → Demande terminée

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-6.1 | En tant que **porteur**, je veux soumettre une demande de dépense (rubrique, montant, objet, description, justificatif PDF) | 🔴 P0 | S3 |
| US-6.2 | En tant que **système**, le porteur ne peut pas soumettre une nouvelle demande sur une convention ayant déjà une demande en cours | 🔴 P0 | S3 |
| US-6.3 | En tant que **porteur**, je veux voir la liste de toutes mes demandes avec leur statut et filtres | 🔴 P0 | S3 |
| US-6.4 | En tant que **daf**, je veux voir les demandes en attente de ma validation | 🔴 P0 | S3 |
| US-6.5 | En tant que **daf**, je veux valider ou rejeter une demande avec un motif | 🔴 P0 | S3 |
| US-6.6 | En tant que **ac**, je veux voir les demandes validées par la DAF en attente de mon approbation | 🔴 P0 | S3 |
| US-6.7 | En tant que **ac**, je veux valider ou rejeter une demande (avec motif) | 🔴 P0 | S3 |
| US-6.8 | En tant que **ac**, après validation, je veux enregistrer le paiement (montant, date, mode, référence) | 🔴 P0 | S3 |
| US-6.9 | En tant que **porteur**, après paiement, je veux uploader le rapport d'exécution (PDF) | 🟠 P1 | S3 |
| US-6.10 | En tant que **daf/ac**, je veux valider le rapport uploadé par le porteur pour clôturer la demande | 🟠 P1 | S3 |
| US-6.11 | En tant que **porteur/daf/ac**, je veux télécharger le justificatif ou le rapport PDF | 🟠 P1 | S3 |
| US-6.12 | En tant qu'**utilisateur**, je veux recevoir une notification in-app quand une demande change de statut | 🟠 P1 | S3 |

---

### EPIC 7 : Paiements directs

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-7.1 | En tant que **porteur**, je veux enregistrer un paiement direct du bailleur (montant, description, objet, date) | 🔴 P0 | S3 |
| US-7.2 | En tant que **système**, un paiement direct diminue les fonds prévus par le bailleur et les fonds disponibles du projet | 🔴 P0 | S3 |
| US-7.3 | En tant que **daf/porteur**, je veux voir l'historique des paiements directs par convention | 🟠 P1 | S3 |

---

### EPIC 8 : Tableaux de bord et reporting

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-8.1 | En tant que **daf**, je veux un tableau de bord global (projets actifs, budgets, demandes en attente, graphiques) | 🔴 P0 | S4 |
| US-8.2 | En tant que **porteur**, je veux un dashboard (mes projets, budgets restants, demandes en cours) | 🔴 P0 | S4 |
| US-8.3 | En tant que **ac**, je veux un dashboard (demandes en attente, paiements récents) | 🔴 P0 | S4 |
| US-8.4 | En tant que **daf**, je veux visualiser l'exécution budgétaire par convention et par rubrique (graphiques) | 🟠 P1 | S4 |
| US-8.5 | En tant que **daf**, je veux clôturer un projet et voir les écarts temps et budget (prévu vs réel) | 🟠 P1 | S4 |
| US-8.6 | En tant que **daf/porteur**, je veux générer un rapport de clôture au format **PDF ou Excel** (au choix) | 🟠 P1 | S4 |
| US-8.7 | En tant que **daf**, je veux un rapport d'exécution budgétaire exportable (PDF/Excel) | 🟠 P1 | S4 |
| US-8.8 | En tant que **daf/porteur/ac**, je veux voir la timeline complète d'une demande (soumission → paiement) | 🟡 P2 | S4 |

---

### EPIC 9 : Assistance

| ID | User Story | Priorité | Sprint |
|----|-----------|----------|--------|
| US-9.1 | En tant que **porteur**, je veux accéder à une page FAQ avec les réponses aux questions fréquentes | 🟠 P1 | S4 |
| US-9.2 | En tant que **daf/ac**, je veux envoyer un message à l'admin via un chat intégré | 🟠 P1 | S4 |
| US-9.3 | En tant qu'**admin**, je veux voir et répondre aux messages des DAF/AC | 🟠 P1 | S4 |
| US-9.4 | En tant que **daf/ac**, je veux voir l'historique de mes conversations avec l'admin | 🟡 P2 | S4 |
| US-9.5 | En tant qu'**admin**, je veux gérer les questions/réponses de la FAQ (CRUD) | 🟡 P2 | S4 |

---

## 7. Découpage en sprints

### 🗓️ Sprint 1 — Fondation (Semaines 1-2 : 19 avril → 2 mai 2026)

**Objectif** : Authentification, gestion des utilisateurs, administration système, layout principal.

| # | Tâche | User Stories | Effort |
|---|-------|-------------|--------|
| 1 | Login/logout + middleware par rôle + redirection | US-1.1, US-1.2 | 1j |
| 2 | Layout principal (sidebar, navbar) par rôle | — | 1.5j |
| 3 | CRUD utilisateurs (admin) + filtres | US-1.3, US-1.4, US-1.5, US-1.6 | 2j |
| 4 | Page profil porteur (photo, infos, mdp) | US-1.7, US-1.8 | 1j |
| 5 | Mode maintenance (middleware + settings + auto-off) | US-2.1, US-2.2, US-2.3 | 1.5j |
| 6 | Système d'audit (Observer/Event global) — backend | US-2.4 (backend) | 1j |
| 7 | Pages dashboard vides par rôle | — | 0.5j |
| 8 | Tests Sprint 1 | — | 1.5j |

**Livrable** : Système fonctionnel avec auth, gestion utilisateurs, maintenance, audit backend.

---

### 🗓️ Sprint 2 — Données de base, budget et versements (Semaines 3-4 : 3 → 16 mai 2026)

**Objectif** : Modèles métier, seeders détaillés, consultation projets/conventions, rubriques, versements.

| # | Tâche | User Stories | Effort |
|---|-------|-------------|--------|
| 1 | Modèles + migrations (Projet, Convention, Bailleur, Rubrique, Versement) | — | 1j |
| 2 | Factories + Seeders super détaillés (données réalistes Markdown) | US-3.1, US-3.2, US-3.3 | 1.5j |
| 3 | Page « Mes Projets » porteur (liste + recherche + filtres) | US-3.4 | 1j |
| 4 | Page « Détail projet » (stats + conventions) avec rendu Markdown | US-3.5, US-3.6, US-3.9 | 1.5j |
| 5 | Page « Détail convention » (stats + rubriques + historiques) | US-3.6 | 1j |
| 6 | Vue projets DAF (tous projets + stats détaillées) | US-3.7, US-3.8 | 1j |
| 7 | CRUD rubriques budgétaires (DAF) + contrôle somme ≤ convention | US-4.1, US-4.2, US-4.5 | 1j |
| 8 | Enregistrement + historique versements (DAF) | US-5.1, US-5.2, US-5.3 | 1j |
| 9 | Journal d'audit (page admin, frontend) | US-2.4 (frontend) | 0.5j |
| 10 | Conversion devise statique | US-3.10 | 0.5j |
| 11 | Tests Sprint 2 | — | 1j |

**Livrable** : Données métier en place, consultation projets/conventions fonctionnelle, budget et versements gérés.

---

### 🗓️ Sprint 3 — Circuit des dépenses (Semaines 5-6 : 17 → 30 mai 2026)

**Objectif** : Circuit complet de validation, paiements directs, notifications, upload PDF.

| # | Tâche | User Stories | Effort |
|---|-------|-------------|--------|
| 1 | Modèles + migrations (DemandeDepense, Paiement, PaiementDirect) | — | 0.5j |
| 2 | Formulaire soumission demande (porteur) + contrôle unicité par convention | US-6.1, US-6.2 | 1.5j |
| 3 | Page « Mes Demandes » porteur (liste, filtres, statuts) | US-6.3 | 1j |
| 4 | Page « Demandes » DAF (en attente + historique) + validation/rejet | US-6.4, US-6.5 | 1j |
| 5 | Page « Demandes » AC + validation/rejet + enregistrement paiement | US-6.6, US-6.7, US-6.8 | 1j |
| 6 | Upload rapport post-paiement + validation DAF/AC du rapport | US-6.9, US-6.10 | 1j |
| 7 | Contrôle automatique dépassement budgétaire | US-4.3, US-4.4 | 0.5j |
| 8 | Upload/téléchargement justificatif PDF | US-6.11 | 0.5j |
| 9 | Paiements directs (enregistrement + impact budget) | US-7.1, US-7.2, US-7.3 | 1j |
| 10 | Notifications in-app (changement statut demande) | US-6.12 | 1j |
| 11 | Modification/annulation versement (DAF) | US-5.4 | 0.5j |
| 12 | Tests Sprint 3 | — | 1.5j |

**Livrable** : Circuit complet porteur → DAF → AC fonctionnel, paiements directs, notifications.

---

### 🗓️ Sprint 4 — Dashboards, reporting, assistance et polish (Semaines 7-8 : 31 mai → 14 juin 2026)

**Objectif** : Tableaux de bord, rapports, chat, FAQ, clôture projet, finitions.

| # | Tâche | User Stories | Effort |
|---|-------|-------------|--------|
| 1 | Dashboard DAF (stats globales + graphiques + alertes) | US-8.1, US-8.4 | 2j |
| 2 | Dashboard porteur (mes projets, budgets, demandes) | US-8.2 | 1j |
| 3 | Dashboard AC (demandes en attente, paiements récents) | US-8.3 | 0.5j |
| 4 | Dashboard admin (stats globales système) | US-2.5 | 0.5j |
| 5 | Clôture de projet + analyse écarts (temps + budget) | US-8.5 | 1j |
| 6 | Génération rapports PDF/Excel (exécution budgétaire + clôture) | US-8.6, US-8.7 | 1.5j |
| 7 | Timeline traçabilité d'une demande | US-8.8 | 0.5j |
| 8 | FAQ porteur (page statique, accordéon) | US-9.1 | 0.5j |
| 9 | Chat DAF/AC ↔ Admin | US-9.2, US-9.3, US-9.4 | 1.5j |
| 10 | Gestion FAQ admin (CRUD) | US-9.5 | 0.5j |
| 11 | Tests finaux + corrections bugs + polish UI | — | 1.5j |

**Livrable** : Application complète avec dashboards, reporting, assistance, prête pour démonstration.

---

## 8. Stratégie de seeders

Les seeders doivent être **très détaillés et réalistes** pour simuler un environnement de production.

### 8.1 Données à générer

| Entité | Quantité | Détails |
|---|---|---|
| **Admin** | 1 | `admin@cifeu.bf` / mot de passe connu |
| **DAF** | 1 | `daf@cifeu.bf` |
| **AC** | 1 | `ac@cifeu.bf` |
| **Porteurs** | 8-10 | Noms réalistes d'enseignants-chercheurs |
| **Bailleurs** | 5-6 | Banque Mondiale, AFD, UEMOA, BAD, Coopération Suisse, UE |
| **Projets** | 10-12 | Descriptions Markdown détaillées, différents statuts |
| **Conventions** | 15-20 | Liées aux projets/bailleurs, montants réalistes en FCFA |
| **Rubriques** | 40-60 | 3-5 par convention (matériel, missions, prestations, fonctionnement, etc.) |
| **Versements** | 20-30 | Différents types et dates |
| **Demandes** | 15-20 | À différents stades du circuit (soumise, validée, rejetée, payée) |
| **Paiements** | 8-10 | Liés aux demandes terminées |
| **Paiements directs** | 5-8 | Paiements bailleurs directs |

### 8.2 Exemples de projets réalistes à seeder

Les descriptions doivent être en **Markdown** avec une structure riche :

1. **PAES-UJKZ** — Programme d'Appui à l'Enseignement Supérieur (en cours, 500M FCFA)
2. **PRESAR** — Projet de Recherche sur la Sécurité Alimentaire au Sahel (en cours, 250M FCFA)
3. **FORMASUP** — Formation et Renforcement des Capacités Pédagogiques (terminé, 180M FCFA)
4. **AQUA-SAHEL** — Gestion Durable des Ressources en Eau (en attente, 320M FCFA)
5. **INNOV-SANTE** — Innovation en Santé Publique Communautaire (annulé, 150M FCFA)
6. **BIODIV-BF** — Conservation de la Biodiversité au Burkina Faso (en cours, 420M FCFA)
7. **NTIC-EDU** — Numérique et Technologies Éducatives (en cours, 200M FCFA)
8. **AGRI-SMART** — Agriculture Intelligente face au Changement Climatique (suspendu, 280M FCFA)
9. **GENRE-DEV** — Genre et Développement Inclusif (en attente, 160M FCFA)
10. **ENERGY-SOLAR** — Énergie Solaire pour les Campus Universitaires (en cours, 350M FCFA)

### 8.3 Exemples de bailleurs

| Bailleur | Sigle | Pays/Organisme | Type |
|---|---|---|---|
| Banque Mondiale | BM | International | Multilatéral |
| Agence Française de Développement | AFD | France | Bilatéral |
| Union Économique et Monétaire Ouest-Africaine | UEMOA | Régional | Multilatéral |
| Banque Africaine de Développement | BAD | Continental | Multilatéral |
| Direction du Développement et de la Coopération | DDC | Suisse | Bilatéral |
| Union Européenne | UE | International | Multilatéral |

---

## 9. Perspectives futures

| Fonctionnalité | Description |
|---|---|
| 📧 Notifications email | Envoi d'emails lors des changements de statut |
| 🔑 Mot de passe oublié | Réinitialisation du mot de passe par email |
| 🔗 Connexion Google/SSO | Auth via Google (vérification email existant uniquement) |
| 💱 Taux de conversion API | Conversion de devises en temps réel via API externe |
| 🔌 Intégration CIFEU | Connexion aux modules Projets et Conventions via API |
| 🔐 LDAP/SSO universitaire | Connexion à l'annuaire UJKZ |
| 📊 Dashboard bailleur | Interface de consultation pour les bailleurs de fonds |
| 📱 Application mobile | Version mobile de l'application |

---

## Annexe A : Circuit de validation des dépenses (détaillé)

```
 ┌─────────────────────────────────────────────────────────────────┐
 │                    CIRCUIT DE VALIDATION                        │
 ├─────────────────────────────────────────────────────────────────┤
 │                                                                 │
 │  1️⃣  PORTEUR soumet la demande                                 │
 │     └─ rubrique, montant, objet, description, justificatif PDF  │
 │     └─ ⚠️ Bloqué si demande déjà en cours sur cette convention │
 │                         │                                       │
 │                         ▼                                       │
 │  2️⃣  DAF vérifie (1er niveau)                                  │
 │     └─ Conformité au projet                                    │
 │     └─ Disponibilité budget dans la rubrique                   │
 │     └─ Cohérence des informations                              │
 │     ├─ ✅ Valide → étape 3                                     │
 │     └─ ❌ Rejette (avec motif) → PORTEUR notifié → FIN         │
 │                         │                                       │
 │                         ▼                                       │
 │  3️⃣  AC vérifie (2ème niveau — contrôle comptable)             │
 │     ├─ ✅ Valide → étape 4                                     │
 │     └─ ❌ Rejette (avec motif) → PORTEUR notifié → FIN         │
 │                         │                                       │
 │                         ▼                                       │
 │  4️⃣  AC enregistre le PAIEMENT                                 │
 │     └─ montant, date, mode (virement/chèque/espèces), référence│
 │     └─ Budget de la rubrique mis à jour automatiquement        │
 │                         │                                       │
 │                         ▼                                       │
 │  5️⃣  PORTEUR uploade le RAPPORT d'exécution (PDF)              │
 │                         │                                       │
 │                         ▼                                       │
 │  6️⃣  DAF + AC valident le rapport                              │
 │     ├─ ✅ Les deux valident → DEMANDE TERMINÉE ✅               │
 │     └─ ❌ Rejet → PORTEUR doit re-uploader                     │
 │                                                                 │
 └─────────────────────────────────────────────────────────────────┘


 Circuit paiement direct (HORS validation) :

 ┌──────────────────────────────────────────────────────┐
 │  PORTEUR enregistre un paiement direct du bailleur   │
 │  └─ montant, description, objet, date                │
 │  └─ Impact : ↓ fonds prévus bailleur                 │
 │              ↓ fonds disponibles projet               │
 │  └─ PAS de circuit DAF/AC                            │
 └──────────────────────────────────────────────────────┘
```

---

## Annexe B : Statuts des demandes (machine à états)

```
                    ┌──────────┐
                    │ SOUMISE  │
                    └────┬─────┘
                         │
                    ┌────▼─────┐        ┌──────────────┐
                    │   DAF    │───❌──→│ REJETÉE_DAF  │ (motif)
                    └────┬─────┘        └──────────────┘
                         │ ✅
                    ┌────▼─────┐        ┌──────────────┐
                    │   AC     │───❌──→│ REJETÉE_AC   │ (motif)
                    └────┬─────┘        └──────────────┘
                         │ ✅
                    ┌────▼─────┐
                    │  PAYÉE   │ (AC enregistre paiement)
                    └────┬─────┘
                         │
                    ┌────▼──────────┐
                    │ RAPPORT_SOUMIS│ (porteur uploade rapport)
                    └────┬──────────┘
                         │
                    ┌────▼──────────┐
                    │   TERMINÉE    │ (DAF + AC valident rapport)
                    └───────────────┘
```

---

> **Document mis à jour le 19 avril 2026**
> **Prochaine étape** : Validation du plan → Sprint 1