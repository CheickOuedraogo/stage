<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut TYPE statut_convention USING convention_statut::text::statut_convention');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_statut SET DEFAULT 'active'");

        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme TYPE forme_convention USING convention_forme::text::forme_convention');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_forme SET DEFAULT 'don'");

        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut DROP DEFAULT');
        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut TYPE statut_demande USING demande_statut::text::statut_demande');
        DB::statement("ALTER TABLE demandes_depense ALTER COLUMN demande_statut SET DEFAULT 'soumise'");

        DB::statement('ALTER TABLE paiements ALTER COLUMN paiement_mode TYPE mode_paiement USING paiement_mode::text::mode_paiement');

        DB::statement('ALTER TABLE notifications ALTER COLUMN type_notification TYPE type_notification USING type_notification::text::type_notification');

        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut DROP DEFAULT');
        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut TYPE statut_projet USING projet_statut::text::statut_projet');
        DB::statement("ALTER TABLE projets ALTER COLUMN projet_statut SET DEFAULT 'en_attente_financement'");

        DB::statement('ALTER TABLE projets ALTER COLUMN statut_final TYPE statut_final_projet USING statut_final::text::statut_final_projet');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut TYPE varchar USING convention_statut::text');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_statut SET DEFAULT 'active'");

        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme TYPE varchar USING convention_forme::text');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_forme SET DEFAULT 'don'");

        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut DROP DEFAULT');
        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut TYPE varchar USING demande_statut::text');
        DB::statement("ALTER TABLE demandes_depense ALTER COLUMN demande_statut SET DEFAULT 'soumise'");

        DB::statement('ALTER TABLE paiements ALTER COLUMN paiement_mode TYPE varchar USING paiement_mode::text');

        DB::statement('ALTER TABLE notifications ALTER COLUMN type_notification TYPE varchar USING type_notification::text');

        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut DROP DEFAULT');
        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut TYPE varchar USING projet_statut::text');
        DB::statement("ALTER TABLE projets ALTER COLUMN projet_statut SET DEFAULT 'en_attente_financement'");
        DB::statement('ALTER TABLE projets ALTER COLUMN statut_final TYPE varchar USING statut_final::text');
    }
};
