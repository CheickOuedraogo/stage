<?php

namespace App\Http\Controllers\Administrateur;

use App\Http\Controllers\Controller;
use App\Services\MaintenanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenanceService) {}

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active' => ['required', 'boolean'],
            'reason' => ['required_if:active,true', 'nullable', 'string', 'max:500'],
            'until' => ['nullable', 'date_format:Y-m-d H:i', 'after:now'],
            'timezone' => ['nullable', 'timezone'],
        ], [
            'reason.required_if' => 'La raison de la maintenance est obligatoire.',
            'until.date_format' => 'Utilisez le format AAAA-MM-JJ HH:MM.',
            'until.after' => 'La date de fin doit être dans le futur.',
        ]);

        if ($validated['active']) {
            $this->maintenanceService->activer(
                raison: $validated['reason'] ?? 'Maintenance en cours.',
                jusqua: isset($validated['until'])
                    ? Carbon::createFromFormat(
                        'Y-m-d H:i',
                        $validated['until'],
                        $validated['timezone'] ?? config('app.timezone'),
                    )->utc()->toIso8601String()
                    : null,
            );

            return back()->with('success', 'Mode maintenance activé. Tous les utilisateurs ont été déconnectés.');
        }

        $this->maintenanceService->desactiver();

        return back()->with('success', 'Mode maintenance désactivé.');
    }
}
