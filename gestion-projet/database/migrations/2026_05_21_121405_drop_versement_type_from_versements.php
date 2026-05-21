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
        Schema::table('versements', function (Blueprint $table) {
            $table->dropColumn('versement_type');
        });
    }

    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->string('versement_type', 20)->default('tranche')->after('versement_montant');
        });
    }
};
