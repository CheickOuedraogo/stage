<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function admin(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'users_by_role' => collect(UserRole::cases())->map(fn ($r) => [
                    'role' => $r->shortLabel(),
                    'label' => $r->label(),
                    'count' => User::byRole($r)->count(),
                ])->all(),
            ],
        ]);
    }

    public function daf(): Response
    {
        return Inertia::render('daf/Dashboard');
    }

    public function ac(): Response
    {
        return Inertia::render('ac/Dashboard');
    }

    public function porteur(): Response
    {
        return Inertia::render('porteur/Dashboard');
    }
}
