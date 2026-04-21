<?php

namespace App\Enums;

enum DemandeStatus: string
{
    case Soumise = 'soumise';
    case ValidéeDaf = 'validee_daf';
    case RejetéeDaf = 'rejetee_daf';
    case ValidéeAc = 'validee_ac';
    case RejetéeAc = 'rejetee_ac';
    case Payee = 'payee';
    case RapportSoumis = 'rapport_soumis';
    case Terminee = 'terminee';

    public function label(): string
    {
        return match ($this) {
            self::Soumise => 'Soumise',
            self::ValidéeDaf => 'Validée DAF',
            self::RejetéeDaf => 'Rejetée DAF',
            self::ValidéeAc => 'Validée AC',
            self::RejetéeAc => 'Rejetée AC',
            self::Payee => 'Payée',
            self::RapportSoumis => 'Rapport soumis',
            self::Terminee => 'Terminée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Soumise => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            self::ValidéeDaf => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::RejetéeDaf => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            self::ValidéeAc => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::RejetéeAc => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            self::Payee => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            self::RapportSoumis => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            self::Terminee => 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
        };
    }

    /** Terminal states — demande is no longer "active" */
    public function isTerminal(): bool
    {
        return in_array($this, [self::RejetéeDaf, self::RejetéeAc, self::Terminee]);
    }
}
