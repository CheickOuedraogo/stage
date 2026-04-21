<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('redirige vers la connexion si non authentifié', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});
