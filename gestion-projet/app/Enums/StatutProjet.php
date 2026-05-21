<?php

namespace App\Enums;

enum StatutProjet: string
{
    case EnAttenteFinancement = 'en_attente_financement';
    case EnCours = 'en_cours';
    case Suspendu = 'suspendu';
    case Termine = 'termine';
    case Annule = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::EnAttenteFinancement => 'En attente de financement',
            self::EnCours => 'En cours',
            self::Suspendu => 'Suspendu',
            self::Termine => 'Terminé',
            self::Annule => 'Annulé',
        };
    }

    /** Tailwind color classes for badge */
    public function badgeClass(): string
    {
        return match ($this) {
            self::EnAttenteFinancement => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::EnCours => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::Suspendu => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            self::Termine => 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
            self::Annule => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        };
    }
}
