<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);
echo "Status for / : " . $response->getStatusCode() . "\n";

$request = Illuminate\Http\Request::create('/connexion', 'GET');
$response = $kernel->handle($request);
echo "Status for /connexion : " . $response->getStatusCode() . "\n";
