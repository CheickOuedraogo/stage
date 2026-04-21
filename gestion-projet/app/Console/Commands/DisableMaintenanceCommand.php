<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Console\Command;

class DisableMaintenanceCommand extends Command
{
    protected $signature = 'maintenance:check-auto-disable';

    protected $description = 'Auto-disable maintenance mode if the scheduled end time has passed.';

    public function handle(): int
    {
        $until = Setting::getMaintenanceUntil();

        if (! Setting::isMaintenanceActive()) {
            return self::SUCCESS;
        }

        if ($until && $until->isPast()) {
            Setting::set('maintenance_mode', 'false');
            Setting::set('maintenance_reason', '');
            Setting::set('maintenance_until', '');

            $this->info('Mode maintenance désactivé automatiquement.');

            AuditLog::log('maintenance_disabled', description: 'Mode maintenance désactivé automatiquement (heure prévue atteinte).');
        }

        return self::SUCCESS;
    }
}
