<?php

use App\Http\Middleware\EnsureUtilisateurIsActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\MaintenanceMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureUtilisateurIsActive::class,
            MaintenanceMiddleware::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                if ($request->wantsJson()) {
                    return $response;
                }

                return Inertia::render('Errors/Index', [
                    'status' => $response->getStatusCode(),
                ])->toResponse($request)->setStatusCode($response->getStatusCode());
            } elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'La session a expiré, veuillez recharger la page.',
                ]);
            }

            return $response;
        });
    })->create();
