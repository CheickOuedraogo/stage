<?php

namespace App\Enums;

enum TypeNotification: string
{
    case DemandeStatutChange = 'demande_statut_change';
    case NouvelleDemande = 'nouvelle_demande';
    case PaiementEffectue = 'paiement_effectue';
    case RapportSoumis = 'rapport_soumis';
    case ProjetCloture = 'projet_cloture';
    case ProjetMisEnCours = 'projet_mis_en_cours';
    case UtilisateurCree = 'utilisateur_cree';

    public function label(): string
    {
        return match ($this) {
            self::DemandeStatutChange => 'Changement de statut de demande',
            self::NouvelleDemande => 'Nouvelle demande de dépense',
            self::PaiementEffectue => 'Paiement effectué',
            self::RapportSoumis => 'Rapport soumis',
            self::ProjetCloture => 'Clôture de projet',
            self::ProjetMisEnCours => 'Projet mis en cours',
            self::UtilisateurCree => 'Nouvel utilisateur',
        };
    }
}
