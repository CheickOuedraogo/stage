<?php

namespace App\Console\Commands;

use App\Models\JournalAudit;
use App\Models\Parametre;
use Illuminate\Console\Command;

class DisableMaintenanceCommand extends Command
{
    protected $signature = 'maintenance:check-auto-disable';

    protected $description = 'Auto-disable maintenance mode if the scheduled end time has passed.';

    public function handle(): int
    {
        $until = Parametre::getMaintenanceJusqua();

        if (! Parametre::estMaintenanceActive()) {
            return self::SUCCESS;
        }

        if ($until && $until->isPast()) {
            Parametre::set('maintenance_mode', 'false');
            Parametre::set('maintenance_reason', '');
            Parametre::set('maintenance_until', '');

            $this->info('Mode maintenance désactivé automatiquement.');

            JournalAudit::log('maintenance_disabled', description: 'Mode maintenance désactivé automatiquement (heure prévue atteinte).');
        }

        return self::SUCCESS;
    }
}
