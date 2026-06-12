<?php

require_once __DIR__.'/vendor/autoload.php';

$basePath = __DIR__.'/bootstrap/app.php';
require_once $basePath;

use App\Enums\StatutConvention;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Utilisateur;
use App\Services\ProjetService;
use Illuminate\Validation\ValidationException;

// Seeders are already loaded, use existing data
$porteur = Utilisateur::factory()->porteur()->create();

$projet = Projet::factory()->create([
    'id_porteur' => $porteur->id_utilisateur,
    'projet_statut' => StatutProjet::EnAttenteFinancement->value,
]);

$convention = Convention::factory()->for($projet)->create([
    'convention_statut' => StatutConvention::Active->value,
]);

$projet->update(['projet_statut' => StatutProjet::EnCours->value]);
$projet->refresh();

$service = app(ProjetService::class);

$projet->loadMissing('conventions');

$blocker = $service->getBlockersMettreEnCours($projet);

echo 'Projet statut: '.$projet->projet_statut->value."\n";
echo 'Blocker: '.($blocker ?? 'null')."\n";

try {
    $service->verifierConditionsMettreEnCours($projet);
    echo "No exception thrown\n";
} catch (ValidationException $e) {
    echo "ValidationException thrown\n";
    print_r($e->errors());
}

$projet->update(['projet_statut' => StatutProjet::EnCours->value]);
