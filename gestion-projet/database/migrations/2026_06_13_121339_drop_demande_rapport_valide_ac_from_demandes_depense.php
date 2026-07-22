<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('demandes_depense', function (Blueprint $table) {
            $table->dropColumn('demande_rapport_valide_ac');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_depense', function (Blueprint $table) {
            $table->boolean('demande_rapport_valide_ac')->default(false);
        });
    }
};
