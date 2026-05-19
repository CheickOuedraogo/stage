<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Enable maintenance mode and invalidate all non-admin sessions.
     */
    public function enable(string $reason, ?string $until = null): void
    {
        Setting::set('maintenance_mode', 'true');
        Setting::set('maintenance_reason', $reason);
        Setting::set('maintenance_until', $until ?? '');

        // Invalidate all sessions except admin
        $adminIds = User::where('utilisateur_role', 'admin')->pluck('id_utilisateur');

        DB::table('sessions')
            ->whereNotIn('user_id', $adminIds)
            ->delete();
    }

    /**
     * Disable maintenance mode.
     */
    public function disable(): void
    {
        Setting::set('maintenance_mode', 'false');
        Setting::set('maintenance_reason', '');
        Setting::set('maintenance_until', '');
    }

    /**
     * @return array{active: bool, reason: string|null, until: string|null}
     */
    public function getStatus(): array
    {
        return [
            'active' => Setting::isMaintenanceActive(),
            'reason' => Setting::get('maintenance_reason'),
            'until' => Setting::get('maintenance_until'),
        ];
    }
}
