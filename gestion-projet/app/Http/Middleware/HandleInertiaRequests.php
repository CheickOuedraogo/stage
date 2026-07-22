<?php

namespace App\Http\Middleware;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutDemande;
use App\Models\DemandeDepense;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'nom_application' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id_utilisateur,
                    'utilisateur_nom' => $user->utilisateur_nom,
                    'utilisateur_email' => $user->utilisateur_email,
                    'role_key' => $user->role_key?->value,
                    'label_role' => $user->role_key?->shortLabel(),
                    'utilisateur_actif' => $user->utilisateur_actif,
                    'url_avatar' => $user->url_avatar,
                    'utilisateur_telephone' => $user->utilisateur_telephone,
                    'notifications_non_lues' => $user->notificationsNonLues()->count(),
                    'actions_a_traiter_count' => $this->getActionsATraiterCount($user),
                    'paiements_a_traiter_count' => $this->getPaiementsATraiterCount($user),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
            'maintenance' => [
                'active' => Parametre::estMaintenanceActive(),
                'until' => Parametre::get('maintenance_until'),
                'reason' => Parametre::get('maintenance_reason'),
            ],
        ];
    }

    private function getActionsATraiterCount($user): int
    {
        if ($user->role_key === RoleUtilisateur::Daf) {
            return DemandeDepense::whereIn('demande_statut', [
                StatutDemande::Soumise->value,
                StatutDemande::RapportSoumis->value,
            ])->count();
        }

        if ($user->role_key === RoleUtilisateur::AgentComptable) {
            return DemandeDepense::where('demande_statut', StatutDemande::ValideeDaf->value)->count();
        }

        return 0;
    }

    private function getPaiementsATraiterCount($user): int
    {
        if ($user->role_key === RoleUtilisateur::AgentComptable) {
            return DemandeDepense::where('demande_statut', StatutDemande::ValideeAgentComptable->value)->count();
        }

        return 0;
    }
}
