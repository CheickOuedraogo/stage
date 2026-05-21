<?php

namespace App\Services;

use App\Enums\RoleUtilisateur;
use App\Models\Parametre;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Activer le mode maintenance et invalider toutes les sessions non-admin.
     */
    public function activer(string $raison, ?string $jusqua = null): void
    {
        Parametre::set('maintenance_mode', 'true');
        Parametre::set('maintenance_reason', $raison);
        Parametre::set('maintenance_until', $jusqua ?? '');

        // Invalider toutes les sessions sauf admin
        $adminIds = Utilisateur::parRole(RoleUtilisateur::Administrateur)->pluck('id_utilisateur');

        DB::table('sessions')
            ->whereNotIn('user_id', $adminIds)
            ->delete();
    }

    /**
     * Désactiver le mode maintenance.
     */
    public function desactiver(): void
    {
        Parametre::set('maintenance_mode', 'false');
        Parametre::set('maintenance_reason', '');
        Parametre::set('maintenance_until', '');
    }

    /**
     * @return array{active: bool, raison: string|null, jusqua: string|null}
     */
    public function getStatut(): array
    {
        return [
            'active' => Parametre::estMaintenanceActive(),
            'raison' => Parametre::get('maintenance_reason'),
            'jusqua' => Parametre::get('maintenance_until'),
        ];
    }
}
