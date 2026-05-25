<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        // ── Administrateur ───────────────────────────────────────────────────────────
        Utilisateur::create([
            'utilisateur_nom' => 'Administrateur CIFEU',
            'utilisateur_email' => 'hcheick77@gmail.com',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Administrateur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 56 19 74 06',
        ]);

        // ── DAF ─────────────────────────────────────────────────────────────
        Utilisateur::create([
            'utilisateur_nom' => 'Ouedraogo Bonaventure',
            'utilisateur_email' => 'hcheick75@gmail.com',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Daf,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 25 30 70 01',
        ]);

        // ── AC ──────────────────────────────────────────────────────────────
        Utilisateur::create([
            'utilisateur_nom' => 'Savadofo Kader',
            'utilisateur_email' => 'bouedraogo0412@gmail.com',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::AgentComptable,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 25 30 70 02',
        ]);

        // ── Porteur de projet ───────────────────────────────────────────────
        Utilisateur::create([
            'utilisateur_nom' => 'Pr. Jean-Baptiste OUÉDRAOGO',
            'utilisateur_email' => 'ocheick418@gmail.com',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 11 22 33',
        ]);

        // ── Porteurs de projet supplémentaires (données de démonstration) ──────
        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Aminata TRAORÉ',
            'utilisateur_email' => 'a.traore@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 22 33 44',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Prof. Moussa KABORÉ',
            'utilisateur_email' => 'm.kabore@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 33 44 55',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Fatoumata ZERBO',
            'utilisateur_email' => 'f.zerbo@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 44 55 66',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Ibrahim BAMBARA',
            'utilisateur_email' => 'i.bambara@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 55 66 77',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Prof. Rasmané OUÉDRAOGO',
            'utilisateur_email' => 'r.ouedraogo@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 66 77 88',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Salamata SAWADOGO',
            'utilisateur_email' => 's.sawadogo@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 77 88 99',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Prof. Dieudonné NIKIEMA',
            'utilisateur_email' => 'd.nikiema@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 88 99 00',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Mariam COULIBALY',
            'utilisateur_email' => 'm.coulibaly@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 70 99 00 11',
        ]);

        Utilisateur::create([
            'utilisateur_nom' => 'Prof. Souleymane BOLY',
            'utilisateur_email' => 's.boly@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => true,
            'utilisateur_telephone' => '+226 71 00 11 22',
        ]);

        // ── Porteur inactif pour les tests ─────────────────────────────────────
        Utilisateur::create([
            'utilisateur_nom' => 'Dr. Adama TAPSOBA',
            'utilisateur_email' => 'a.tapsoba@ujkz.bf',
            'utilisateur_mot_de_passe' => Hash::make('utilisateur_mot_de_passe'),
            'role_key' => RoleUtilisateur::Porteur,
            'utilisateur_actif' => false,
            'utilisateur_telephone' => '+226 71 11 22 33',
        ]);
    }
}
