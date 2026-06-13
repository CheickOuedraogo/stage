<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faq', function (Blueprint $table) {
            $table->string('role_key', 50)->nullable()->after('faq_reponse');
        });

        // Priorité : porteur > daf > ac — pour les items multi-rôles existants
        DB::statement("UPDATE faq SET role_key = 'porteur' WHERE visible_porteur = 1");
        DB::statement("UPDATE faq SET role_key = 'daf' WHERE visible_daf = 1 AND role_key IS NULL");
        DB::statement("UPDATE faq SET role_key = 'ac' WHERE visible_ac = 1 AND role_key IS NULL");

        Schema::table('faq', function (Blueprint $table) {
            $table->dropColumn(['faq_actif', 'visible_porteur', 'visible_daf', 'visible_ac']);
        });
    }

    public function down(): void
    {
        Schema::table('faq', function (Blueprint $table) {
            $table->boolean('faq_actif')->default(true);
            $table->boolean('visible_porteur')->default(true);
            $table->boolean('visible_daf')->default(true);
            $table->boolean('visible_ac')->default(true);
        });

        DB::statement("UPDATE faq SET visible_porteur = 1, faq_actif = 1 WHERE role_key = 'porteur'");
        DB::statement("UPDATE faq SET visible_daf = 1, faq_actif = 1 WHERE role_key = 'daf'");
        DB::statement("UPDATE faq SET visible_ac = 1, faq_actif = 1 WHERE role_key = 'ac'");

        Schema::table('faq', function (Blueprint $table) {
            $table->dropColumn('role_key');
        });
    }
};
