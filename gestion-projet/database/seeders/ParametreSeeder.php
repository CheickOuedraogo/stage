<?php

namespace Database\Seeders;

use App\Models\Parametre;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['parametre_cle' => 'maintenance_mode', 'parametre_valeur' => 'false'],
            ['parametre_cle' => 'maintenance_reason', 'parametre_valeur' => ''],
            ['parametre_cle' => 'maintenance_until', 'parametre_valeur' => ''],
        ];

        foreach ($settings as $setting) {
            Parametre::updateOrCreate(
                ['parametre_cle' => $setting['parametre_cle']],
                ['parametre_valeur' => $setting['parametre_valeur']],
            );
        }
    }
}
