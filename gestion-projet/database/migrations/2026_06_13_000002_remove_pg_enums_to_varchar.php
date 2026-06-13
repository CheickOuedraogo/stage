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

        // ── Convertir toutes les colonnes ENUM en VARCHAR ────────────────

        // utilisateurs.role_key
        DB::statement('ALTER TABLE utilisateurs ALTER COLUMN role_key DROP DEFAULT');
        DB::statement('ALTER TABLE utilisateurs ALTER COLUMN role_key TYPE VARCHAR(50) USING role_key::text');
        DB::statement("ALTER TABLE utilisateurs ALTER COLUMN role_key SET DEFAULT 'porteur'");

        // conventions.convention_statut
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_statut TYPE VARCHAR(50) USING convention_statut::text');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_statut SET DEFAULT 'active'");

        // conventions.convention_forme
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme DROP DEFAULT');
        DB::statement('ALTER TABLE conventions ALTER COLUMN convention_forme TYPE VARCHAR(50) USING convention_forme::text');
        DB::statement("ALTER TABLE conventions ALTER COLUMN convention_forme SET DEFAULT 'don'");

        // demandes_depense.demande_statut
        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut DROP DEFAULT');
        DB::statement('ALTER TABLE demandes_depense ALTER COLUMN demande_statut TYPE VARCHAR(50) USING demande_statut::text');
        DB::statement("ALTER TABLE demandes_depense ALTER COLUMN demande_statut SET DEFAULT 'soumise'");

        // paiements.paiement_mode
        DB::statement('ALTER TABLE paiements ALTER COLUMN paiement_mode TYPE VARCHAR(50) USING paiement_mode::text');

        // paiements.type_paiement — migrer les données avant de changer le type
        DB::statement("UPDATE paiements SET type_paiement = 'normal' WHERE type_paiement = 'indirect'");
        DB::statement('ALTER TABLE paiements ALTER COLUMN type_paiement DROP DEFAULT');
        DB::statement('ALTER TABLE paiements ALTER COLUMN type_paiement TYPE VARCHAR(50) USING type_paiement::text');
        DB::statement("ALTER TABLE paiements ALTER COLUMN type_paiement SET DEFAULT 'normal'");

        // notifications.type_notification
        DB::statement('ALTER TABLE notifications ALTER COLUMN type_notification TYPE VARCHAR(50) USING type_notification::text');

        // projets.projet_statut
        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut DROP DEFAULT');
        DB::statement('ALTER TABLE projets ALTER COLUMN projet_statut TYPE VARCHAR(50) USING projet_statut::text');
        DB::statement("ALTER TABLE projets ALTER COLUMN projet_statut SET DEFAULT 'en_attente_financement'");

        // projets.statut_final
        DB::statement('ALTER TABLE projets ALTER COLUMN statut_final TYPE VARCHAR(50) USING statut_final::text');

        // ── Supprimer tous les types ENUM PostgreSQL ──────────────────────
        DB::statement('DROP TYPE IF EXISTS type_paiement CASCADE');
        DB::statement('DROP TYPE IF EXISTS type_notification CASCADE');
        DB::statement('DROP TYPE IF EXISTS mode_paiement CASCADE');
        DB::statement('DROP TYPE IF EXISTS statut_demande CASCADE');
        DB::statement('DROP TYPE IF EXISTS forme_convention CASCADE');
        DB::statement('DROP TYPE IF EXISTS statut_convention CASCADE');
        DB::statement('DROP TYPE IF EXISTS role_utilisateur CASCADE');
        DB::statement('DROP TYPE IF EXISTS statut_projet CASCADE');
        DB::statement('DROP TYPE IF EXISTS statut_final_projet CASCADE');
    }

    public function down(): void
    {
        // Impossible de recréer les types ENUM facilement sans connaître les données.
        // La down est intentionnellement vide.
    }
};
