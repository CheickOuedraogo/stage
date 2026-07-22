<?php

namespace App\Console\Commands;

use App\Models\Parametre;
use App\Services\MaintenanceService;
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
            app(MaintenanceService::class)->desactiver();

            $this->info('Mode maintenance désactivé automatiquement.');

        }

        return self::SUCCESS;
    }
}
