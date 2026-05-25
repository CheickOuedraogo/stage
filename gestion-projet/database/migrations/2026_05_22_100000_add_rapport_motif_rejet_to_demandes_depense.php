<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_depense', function (Blueprint $table) {
            $table->text('demande_rapport_motif_rejet')->nullable()->after('demande_rapport');
        });
    }

    public function down(): void
    {
        Schema::table('demandes_depense', function (Blueprint $table) {
            $table->dropColumn('demande_rapport_motif_rejet');
        });
    }
};
