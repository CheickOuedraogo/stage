<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Daf\ConventionController as DafConventionController;
use App\Http\Controllers\Daf\ProjetController as DafProjetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Porteur\ProjetController as PorteurProjetController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── Auth ────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [LoginController::class, 'login']);
});

Route::post('/deconnexion', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Profile (all roles)
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Admin ──────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'admin'])->name('dashboard');

        // Users
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
        Route::get('/utilisateurs/creer', [UserController::class, 'create'])->name('users.create');
        Route::post('/utilisateurs', [UserController::class, 'store'])->name('users.store');
        Route::get('/utilisateurs/{user}/modifier', [UserController::class, 'edit'])->name('users.edit');
        Route::patch('/utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/utilisateurs/{user}/activer', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Maintenance
        Route::patch('/maintenance', [MaintenanceController::class, 'update'])->name('maintenance.update');

        // Audit log
        Route::get('/journal-audit', [AuditLogController::class, 'index'])->name('audit-log');
    });

    // ── DAF ───────────────────────────────────────────────────────────────
    Route::middleware('role:daf')->prefix('daf')->name('daf.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'daf'])->name('dashboard');

        // Projets
        Route::get('/projets', [DafProjetController::class, 'index'])->name('projets.index');
        Route::get('/projets/{projet}', [DafProjetController::class, 'show'])->name('projets.show');

        // Conventions d'un projet
        Route::get('/projets/{projet}/conventions/{convention}', [DafConventionController::class, 'show'])->name('projets.conventions.show');
        Route::post('/projets/{projet}/conventions/{convention}/rubriques', [DafConventionController::class, 'storeRubrique'])->name('projets.conventions.rubriques.store');
        Route::patch('/projets/{projet}/conventions/{convention}/rubriques/{rubrique}', [DafConventionController::class, 'updateRubrique'])->name('projets.conventions.rubriques.update');
        Route::delete('/projets/{projet}/conventions/{convention}/rubriques/{rubrique}', [DafConventionController::class, 'destroyRubrique'])->name('projets.conventions.rubriques.destroy');
        Route::post('/projets/{projet}/conventions/{convention}/versements', [DafConventionController::class, 'storeVersement'])->name('projets.conventions.versements.store');
        Route::delete('/projets/{projet}/conventions/{convention}/versements/{versement}', [DafConventionController::class, 'destroyVersement'])->name('projets.conventions.versements.destroy');
    });

    // ── AC ────────────────────────────────────────────────────────────────
    Route::middleware('role:ac')->prefix('ac')->name('ac.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'ac'])->name('dashboard');
    });

    // ── Porteur ───────────────────────────────────────────────────────────
    Route::middleware('role:porteur')->prefix('porteur')->name('porteur.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'porteur'])->name('dashboard');

        // Projets
        Route::get('/projets', [PorteurProjetController::class, 'index'])->name('projets.index');
        Route::get('/projets/{projet}', [PorteurProjetController::class, 'show'])->name('projets.show');
        Route::get('/projets/{projet}/conventions/{convention}', [PorteurProjetController::class, 'showConvention'])->name('projets.conventions.show');
    });
});

// Home redirect
Route::get('/', function () {
    return Auth::check()
        ? redirect(match (Auth::user()->role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Daf => route('daf.dashboard'),
            UserRole::Ac => route('ac.dashboard'),
            UserRole::Porteur => route('porteur.dashboard'),
        })
        : redirect()->route('login');
})->name('home');
