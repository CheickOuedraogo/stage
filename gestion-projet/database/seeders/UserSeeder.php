<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ───────────────────────────────────────────────────────────
        User::create([
            'name' => 'Administrateur CIFEU',
            'email' => 'admin@cifeu.bf',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
            'telephone' => '+226 25 30 70 00',
        ]);

        // ── DAF ─────────────────────────────────────────────────────────────
        User::create([
            'name' => 'Inoussa SAWADOGO',
            'email' => 'daf@cifeu.bf',
            'password' => Hash::make('password'),
            'role' => UserRole::Daf,
            'is_active' => true,
            'telephone' => '+226 25 30 70 01',
        ]);

        // ── AC ──────────────────────────────────────────────────────────────
        User::create([
            'name' => 'Fatimata COMPAORÉ',
            'email' => 'ac@cifeu.bf',
            'password' => Hash::make('password'),
            'role' => UserRole::Ac,
            'is_active' => true,
            'telephone' => '+226 25 30 70 02',
        ]);

        // ── Porteurs de projet ───────────────────────────────────────────────
        $porteurs = [
            [
                'name' => 'Pr. Jean-Baptiste OUÉDRAOGO',
                'email' => 'jb.ouedraogo@ujkz.bf',
                'telephone' => '+226 70 11 22 33',
            ],
            [
                'name' => 'Dr. Aminata TRAORÉ',
                'email' => 'a.traore@ujkz.bf',
                'telephone' => '+226 70 44 55 66',
            ],
            [
                'name' => 'Pr. Moussa KABORÉ',
                'email' => 'm.kabore@ujkz.bf',
                'telephone' => '+226 71 22 33 44',
            ],
            [
                'name' => 'Dr. Aïssata ZONGO',
                'email' => 'a.zongo@ujkz.bf',
                'telephone' => '+226 70 55 66 77',
            ],
            [
                'name' => 'Pr. Adama COULIBALY',
                'email' => 'a.coulibaly@ujkz.bf',
                'telephone' => '+226 71 33 44 55',
            ],
            [
                'name' => 'Dr. Rasmata KINDA',
                'email' => 'r.kinda@ujkz.bf',
                'telephone' => '+226 70 66 77 88',
            ],
            [
                'name' => 'Pr. Boubacar BARRY',
                'email' => 'b.barry@ujkz.bf',
                'telephone' => '+226 71 44 55 66',
            ],
            [
                'name' => 'Dr. Mariam DIALLO',
                'email' => 'm.diallo@ujkz.bf',
                'telephone' => '+226 70 77 88 99',
            ],
            [
                'name' => 'Pr. Souleymane OUATTARA',
                'email' => 's.ouattara@ujkz.bf',
                'telephone' => '+226 71 55 66 77',
            ],
            [
                'name' => 'Dr. Bintou SAWADOGO',
                'email' => 'b.sawadogo@ujkz.bf',
                'telephone' => '+226 70 88 99 00',
            ],
        ];

        foreach ($porteurs as $porteur) {
            User::create([
                ...$porteur,
                'password' => Hash::make('password'),
                'role' => UserRole::Porteur,
                'is_active' => true,
            ]);
        }

        // One inactive porteur for testing
        User::create([
            'name' => 'Ibrahim TAPSOBA',
            'email' => 'i.tapsoba@ujkz.bf',
            'password' => Hash::make('password'),
            'role' => UserRole::Porteur,
            'is_active' => false,
            'telephone' => '+226 70 00 11 22',
        ]);
    }
}
