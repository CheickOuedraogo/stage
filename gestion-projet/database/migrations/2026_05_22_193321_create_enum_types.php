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

        DB::statement('DROP TYPE IF EXISTS role_utilisateur CASCADE');
        DB::statement("CREATE TYPE role_utilisateur AS ENUM ('admin', 'daf', 'ac', 'porteur')");
        DB::statement('DROP TYPE IF EXISTS statut_convention CASCADE');
        DB::statement("CREATE TYPE statut_convention AS ENUM ('active', 'suspendue', 'terminee', 'annulee')");
        DB::statement('DROP TYPE IF EXISTS forme_convention CASCADE');
        DB::statement("CREATE TYPE forme_convention AS ENUM ('pret', 'don')");
        DB::statement('DROP TYPE IF EXISTS statut_demande CASCADE');
        DB::statement("CREATE TYPE statut_demande AS ENUM ('soumise', 'validee_daf', 'rejetee_daf', 'validee_ac', 'rejetee_ac', 'payee', 'rapport_soumis', 'terminee')");
        DB::statement('DROP TYPE IF EXISTS mode_paiement CASCADE');
        DB::statement("CREATE TYPE mode_paiement AS ENUM ('virement', 'cheque', 'especes')");
        DB::statement('DROP TYPE IF EXISTS type_notification CASCADE');
        DB::statement("CREATE TYPE type_notification AS ENUM ('demande_statut_change', 'projet_cloture', 'projet_mis_en_cours')");
        DB::statement('DROP TYPE IF EXISTS statut_projet CASCADE');
        DB::statement("CREATE TYPE statut_projet AS ENUM ('en_attente_financement', 'en_cours', 'suspendu', 'termine', 'annule')");
        DB::statement('DROP TYPE IF EXISTS statut_final_projet CASCADE');
        DB::statement("CREATE TYPE statut_final_projet AS ENUM ('succes', 'echec')");
        DB::statement('DROP TYPE IF EXISTS type_paiement CASCADE');
        DB::statement("CREATE TYPE type_paiement AS ENUM ('direct', 'indirect')");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TYPE IF EXISTS type_paiement');
        DB::statement('DROP TYPE IF EXISTS statut_final_projet');
        DB::statement('DROP TYPE IF EXISTS statut_projet');
        DB::statement('DROP TYPE IF EXISTS type_notification');
        DB::statement('DROP TYPE IF EXISTS mode_paiement');
        DB::statement('DROP TYPE IF EXISTS statut_demande');
        DB::statement('DROP TYPE IF EXISTS forme_convention');
        DB::statement('DROP TYPE IF EXISTS statut_convention');
        DB::statement('DROP TYPE IF EXISTS role_utilisateur');
    }
};
