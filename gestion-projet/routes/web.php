<?php

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Administrateur\MaintenanceController;
use App\Http\Controllers\Administrateur\UtilisateurControleur;
use App\Http\Controllers\AgentComptable\ConventionController as AgentComptableConventionController;
use App\Http\Controllers\AgentComptable\DemandeDepenseController as AgentComptableDemandeDepenseController;
use App\Http\Controllers\AgentComptable\PaiementController as AgentComptablePaiementController;
use App\Http\Controllers\AgentComptable\PaiementDirectController as AgentComptablePaiementDirectController;
use App\Http\Controllers\AgentComptable\ProjetController as AgentComptableProjetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Daf\ConventionController as DafConventionController;
use App\Http\Controllers\Daf\DemandeDepenseController as DafDemandeDepenseController;
use App\Http\Controllers\Daf\ProjetController as DafProjetController;
use App\Http\Controllers\Daf\RapportController as DafRapportController;
use App\Http\Controllers\Daf\RubriqueController as DafRubriqueController;
use App\Http\Controllers\Daf\VersementController as DafVersementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Porteur\DemandeDepenseController as PorteurDemandeDepenseController;
use App\Http\Controllers\Porteur\ProjetController as PorteurProjetController;
use App\Http\Controllers\Profile\ProfileController;
use App\Models\Parametre;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Auth ────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [LoginController::class, 'login']);

});

Route::post('/deconnexion', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/maintenance', function () {
    if (! Parametre::isMaintenanceActive()) {
        return redirect()->route('home');
    }

    return Inertia::render('auth/Maintenance', [
        'reason' => Parametre::get('maintenance_reason', 'Maintenance en cours.'),
        'until' => Parametre::get('maintenance_until'),
    ]);
})->name('maintenance');

// ── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Notifications (all roles)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/lue', [NotificationController::class, 'marquerLue'])->name('notifications.read');
    Route::patch('/notifications/toutes-lues', [NotificationController::class, 'marquerToutesLues'])->name('notifications.read-all');

    // File downloads — accessible by porteur (owner) + daf + ac
    Route::get('/fichiers/demandes/{demande}/justificatif', [PorteurDemandeDepenseController::class, 'downloadJustificatif'])->name('demandes.justificatif.download');
    Route::get('/fichiers/demandes/{demande}/rapport', [PorteurDemandeDepenseController::class, 'downloadRapport'])->name('demandes.rapport.download');

    // Profile (all roles)
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── Administrateur ──────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'admin'])->name('dashboard');

        // Utilisateurs
        Route::get('/utilisateurs', [UtilisateurControleur::class, 'index'])->name('users.index');
        Route::get('/utilisateurs/creer', [UtilisateurControleur::class, 'create'])->name('users.create');
        Route::post('/utilisateurs', [UtilisateurControleur::class, 'store'])->name('users.store');
        Route::get('/utilisateurs/{user}/modifier', [UtilisateurControleur::class, 'edit'])->name('users.edit');
        Route::patch('/utilisateurs/{user}', [UtilisateurControleur::class, 'update'])->name('users.update');
        Route::patch('/utilisateurs/{user}/activer', [UtilisateurControleur::class, 'toggleActive'])->name('users.toggle-active');

        // Maintenance
        Route::patch('/maintenance', [MaintenanceController::class, 'update'])->name('maintenance.update');

    });

    // ── DAF ───────────────────────────────────────────────────────────────
    Route::middleware('role:daf')->prefix('daf')->name('daf.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'daf'])->name('dashboard');

        // Projets
        Route::get('/projets', [DafProjetController::class, 'index'])->name('projets.index');
        Route::get('/projets/{projet}', [DafProjetController::class, 'show'])->name('projets.show');
        Route::get('/projets/{projet}/bilan', [DafProjetController::class, 'bilan'])->name('projets.bilan');
        Route::get('/projets/{projet}/bilan/pdf', [DafProjetController::class, 'exporterBilanPdf'])->name('projets.bilan.pdf');
        Route::get('/projets/{projet}/bilan/excel', [DafProjetController::class, 'exporterBilanExcel'])->name('projets.bilan.excel');

        // Conventions d'un projet
        Route::get('/projets/{projet}/conventions/{convention}', [DafConventionController::class, 'show'])->name('projets.conventions.show');
        Route::post('/projets/{projet}/conventions/{convention}/rubriques', [DafConventionController::class, 'storeRubrique'])->name('projets.conventions.rubriques.store');
        Route::patch('/projets/{projet}/conventions/{convention}/rubriques/{rubrique}', [DafConventionController::class, 'updateRubrique'])->name('projets.conventions.rubriques.update');
        Route::delete('/projets/{projet}/conventions/{convention}/rubriques/{rubrique}', [DafConventionController::class, 'destroyRubrique'])->name('projets.conventions.rubriques.destroy');
        Route::post('/projets/{projet}/conventions/{convention}/versements', [DafConventionController::class, 'storeVersement'])->name('projets.conventions.versements.store');
        Route::patch('/projets/{projet}/conventions/{convention}/versements/{versement}', [DafConventionController::class, 'updateVersement'])->name('projets.conventions.versements.update');
        Route::delete('/projets/{projet}/conventions/{convention}/versements/{versement}', [DafConventionController::class, 'destroyVersement'])->name('projets.conventions.versements.destroy');
        // Versements (vue globale)
        Route::get('/versements', [DafVersementController::class, 'index'])->name('versements.index');

        // Rubriques (vue globale)
        Route::get('/rubriques', [DafRubriqueController::class, 'index'])->name('rubriques.index');

        // Rapports
        Route::get('/rapports', [DafRapportController::class, 'index'])->name('rapports.index');
        Route::get('/rapports/execution-budgetaire', [DafRapportController::class, 'executionBudgetaire'])->name('rapports.execution-budgetaire');
        Route::get('/rapports/cloture', [DafRapportController::class, 'cloture'])->name('rapports.cloture');

        // Demandes de dépenses
        Route::get('/demandes', [DafDemandeDepenseController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/{demande}', [DafDemandeDepenseController::class, 'show'])->name('demandes.show');
        Route::post('/demandes/{demande}/valider', [DafDemandeDepenseController::class, 'valider'])->name('demandes.valider');
        Route::post('/demandes/{demande}/rejeter', [DafDemandeDepenseController::class, 'rejeter'])->name('demandes.rejeter');
        Route::post('/demandes/{demande}/valider-rapport', [DafDemandeDepenseController::class, 'validerRapport'])->name('demandes.valider-rapport');
        Route::post('/demandes/{demande}/rejeter-rapport', [DafDemandeDepenseController::class, 'rejeterRapport'])->name('demandes.rejeter-rapport');
    });

    // ── AC ────────────────────────────────────────────────────────────────
    Route::middleware('role:ac')->prefix('ac')->name('ac.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'ac'])->name('dashboard');

        // Conventions (paiements directs)
        Route::get('/projets/{projet}/conventions/{convention}', [AgentComptableConventionController::class, 'show'])->name('projets.conventions.show');

        // Paiements directs (enregistrés par l'AC)
        Route::post('/projets/{projet}/conventions/{convention}/paiements-directs', [AgentComptablePaiementDirectController::class, 'store'])->name('projets.conventions.paiements-directs.store');
        Route::delete('/projets/{projet}/conventions/{convention}/paiements-directs/{paiementDirect}', [AgentComptablePaiementDirectController::class, 'destroy'])->name('projets.conventions.paiements-directs.destroy');

        // Projets
        Route::get('/projets', [AgentComptableProjetController::class, 'index'])->name('projets.index');
        Route::get('/projets/{projet}', [AgentComptableProjetController::class, 'show'])->name('projets.show');
        // Bilan projet (lecture seule)
        Route::get('/projets/{projet}/bilan', [AgentComptableProjetController::class, 'bilan'])->name('projets.bilan');
        Route::get('/projets/{projet}/bilan/pdf', [AgentComptableProjetController::class, 'exporterBilanPdf'])->name('projets.bilan.pdf');
        Route::get('/projets/{projet}/bilan/excel', [AgentComptableProjetController::class, 'exporterBilanExcel'])->name('projets.bilan.excel');

        // Paiements
        Route::get('/paiements', [AgentComptablePaiementController::class, 'index'])->name('paiements.index');

        // Demandes de dépenses
        Route::get('/demandes', [AgentComptableDemandeDepenseController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/{demande}', [AgentComptableDemandeDepenseController::class, 'show'])->name('demandes.show');
        Route::post('/demandes/{demande}/valider', [AgentComptableDemandeDepenseController::class, 'valider'])->name('demandes.valider');
        Route::post('/demandes/{demande}/rejeter', [AgentComptableDemandeDepenseController::class, 'rejeter'])->name('demandes.rejeter');
        Route::post('/demandes/{demande}/paiement', [AgentComptableDemandeDepenseController::class, 'enregistrerPaiement'])->name('demandes.paiement');
    });

    // ── Porteur ───────────────────────────────────────────────────────────
    Route::middleware('role:porteur')->prefix('porteur')->name('porteur.')->group(function () {
        Route::get('/tableau-de-bord', [DashboardController::class, 'porteur'])->name('dashboard');

        // Projets
        Route::get('/projets', [PorteurProjetController::class, 'index'])->name('projets.index');
        Route::get('/projets/{projet}', [PorteurProjetController::class, 'show'])->name('projets.show');
        Route::get('/projets/{projet}/bilan', [PorteurProjetController::class, 'bilan'])->name('projets.bilan');
        Route::get('/projets/{projet}/bilan/pdf', [PorteurProjetController::class, 'exporterBilanPdf'])->name('projets.bilan.pdf');
        Route::get('/projets/{projet}/bilan/excel', [PorteurProjetController::class, 'exporterBilanExcel'])->name('projets.bilan.excel');
        Route::get('/projets/{projet}/conventions/{convention}', [PorteurProjetController::class, 'showConvention'])->name('projets.conventions.show');

        // Demandes de dépenses
        Route::get('/demandes', [PorteurDemandeDepenseController::class, 'index'])->name('demandes.index');
        Route::get('/projets/{projet}/conventions/{convention}/demandes/creer', [PorteurDemandeDepenseController::class, 'create'])->name('projets.conventions.demandes.create');
        Route::post('/projets/{projet}/conventions/{convention}/demandes', [PorteurDemandeDepenseController::class, 'store'])->name('projets.conventions.demandes.store');
        Route::get('/demandes/{demande}', [PorteurDemandeDepenseController::class, 'show'])->name('demandes.show');
        Route::post('/demandes/{demande}/rapport', [PorteurDemandeDepenseController::class, 'uploadRapport'])->name('demandes.rapport');
        Route::get('/demandes/{demande}/justificatif', [PorteurDemandeDepenseController::class, 'downloadJustificatif'])->name('demandes.justificatif');
        Route::get('/demandes/{demande}/rapport-pdf', [PorteurDemandeDepenseController::class, 'downloadRapport'])->name('demandes.rapport-pdf');

    });
});

// Home redirect
Route::get('/', function () {
    return Auth::check()
        ? redirect(match (Auth::user()->role_key) {
            RoleUtilisateur::Administrateur => route('admin.dashboard'),
            RoleUtilisateur::Daf => route('daf.dashboard'),
            RoleUtilisateur::AgentComptable => route('ac.dashboard'),
            RoleUtilisateur::Porteur => route('porteur.dashboard'),
        })
        : redirect()->route('login');
})->name('home');
