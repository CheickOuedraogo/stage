<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenanceService) {}

    public function index(): Response
    {
        return Inertia::render('admin/Maintenance', [
            'maintenance' => $this->maintenanceService->getStatus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active' => ['required', 'boolean'],
            'reason' => ['required_if:active,true', 'nullable', 'string', 'max:500'],
            'until' => ['nullable', 'date', 'after:now'],
        ], [
            'reason.required_if' => 'La raison de la maintenance est obligatoire.',
            'until.after' => 'La date de fin doit être dans le futur.',
        ]);

        if ($validated['active']) {
            $this->maintenanceService->enable(
                reason: $validated['reason'] ?? 'Maintenance en cours.',
                until: $validated['until'] ?? null,
            );

            AuditLog::log('maintenance_enabled', description: 'Mode maintenance activé par '.auth()->user()->name);

            return back()->with('success', 'Mode maintenance activé. Tous les utilisateurs ont été déconnectés.');
        }

        $this->maintenanceService->disable();

        AuditLog::log('maintenance_disabled', description: 'Mode maintenance désactivé par '.auth()->user()->name);

        return back()->with('success', 'Mode maintenance désactivé.');
    }
}
