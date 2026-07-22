<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE utilisateurs ALTER COLUMN utilisateur_role DROP DEFAULT');
        }

        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->renameColumn('utilisateur_role', 'role_key');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE utilisateurs ALTER COLUMN role_key TYPE role_utilisateur USING role_key::text::role_utilisateur');
            DB::statement("ALTER TABLE utilisateurs ALTER COLUMN role_key SET DEFAULT 'porteur'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE utilisateurs ALTER COLUMN role_key DROP DEFAULT');
            DB::statement('ALTER TABLE utilisateurs ALTER COLUMN role_key TYPE varchar USING role_key::text');
            DB::statement("ALTER TABLE utilisateurs ALTER COLUMN role_key SET DEFAULT 'porteur'");
        }
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->renameColumn('role_key', 'utilisateur_role');
        });
    }
};
