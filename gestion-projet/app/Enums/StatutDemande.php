<?php

namespace App\Enums;

enum StatutDemande: string
{
    case Soumise = 'soumise';
    case ValideeDaf = 'validee_daf';
    case RejeteeDaf = 'rejetee_daf';
    case ValideeAgentComptable = 'validee_ac';
    case RejeteeAgentComptable = 'rejetee_ac';
    case Payee = 'payee';
    case RapportSoumis = 'rapport_soumis';
    case Terminee = 'terminee';

    public function label(): string
    {
        return match ($this) {
            self::Soumise => 'Soumise',
            self::ValideeDaf => 'Validée DAF',
            self::RejeteeDaf => 'Rejetée DAF',
            self::ValideeAgentComptable => 'Validée AC',
            self::RejeteeAgentComptable => 'Rejetée AC',
            self::Payee => 'Payée',
            self::RapportSoumis => 'Rapport soumis',
            self::Terminee => 'Terminée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Soumise => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            self::ValideeDaf => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::RejeteeDaf => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            self::ValideeAgentComptable => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::RejeteeAgentComptable => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            self::Payee => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
            self::RapportSoumis => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            self::Terminee => 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
        };
    }

    /** États terminaux — la demande n'est plus "active" */
    public function estTerminal(): bool
    {
        return in_array($this, [self::RejeteeDaf, self::RejeteeAgentComptable, self::Terminee]);
    }
}
