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
            'email' => 'hcheick77@gmail.com',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Admin,
            'utilisateur_actif' => true,
            'telephone' => '+226 56 19 74 06',
        ]);

        // ── DAF ─────────────────────────────────────────────────────────────
        User::create([
            'name' => 'Ouedraogo Bonaventure',
            'email' => 'hcheick75@gmail.com',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Daf,
            'utilisateur_actif' => true,
            'telephone' => '+226 25 30 70 01',
        ]);

        // ── AC ──────────────────────────────────────────────────────────────
        User::create([
            'name' => 'Savadofo Kader',
            'email' => 'bouedraogo0412@gmail.com',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Ac,
            'utilisateur_actif' => true,
            'telephone' => '+226 25 30 70 02',
        ]);

        // ── Porteur de projet ───────────────────────────────────────────────
        User::create([
            'name' => 'Pr. Jean-Baptiste OUÉDRAOGO',
            'email' => 'ocheick418@gmail.com',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 11 22 33',
        ]);

        // ── Porteurs de projet supplémentaires (données de démonstration) ──────
        User::create([
            'name' => 'Dr. Aminata TRAORÉ',
            'email' => 'a.traore@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 22 33 44',
        ]);

        User::create([
            'name' => 'Prof. Moussa KABORÉ',
            'email' => 'm.kabore@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 33 44 55',
        ]);

        User::create([
            'name' => 'Dr. Fatoumata ZERBO',
            'email' => 'f.zerbo@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 44 55 66',
        ]);

        User::create([
            'name' => 'Dr. Ibrahim BAMBARA',
            'email' => 'i.bambara@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 55 66 77',
        ]);

        User::create([
            'name' => 'Prof. Rasmané OUÉDRAOGO',
            'email' => 'r.ouedraogo@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 66 77 88',
        ]);

        User::create([
            'name' => 'Dr. Salamata SAWADOGO',
            'email' => 's.sawadogo@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 77 88 99',
        ]);

        User::create([
            'name' => 'Prof. Dieudonné NIKIEMA',
            'email' => 'd.nikiema@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 88 99 00',
        ]);

        User::create([
            'name' => 'Dr. Mariam COULIBALY',
            'email' => 'm.coulibaly@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 70 99 00 11',
        ]);

        User::create([
            'name' => 'Prof. Souleymane BOLY',
            'email' => 's.boly@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => true,
            'telephone' => '+226 71 00 11 22',
        ]);

        // ── Porteur inactif pour les tests ─────────────────────────────────────
        User::create([
            'name' => 'Dr. Adama TAPSOBA',
            'email' => 'a.tapsoba@ujkz.bf',
            'password' => Hash::make('password'),
            'utilisateur_role' => UserRole::Porteur,
            'utilisateur_actif' => false,
            'telephone' => '+226 71 11 22 33',
        ]);
    }
}
