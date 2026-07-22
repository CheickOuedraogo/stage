<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rubriques', function (Blueprint $table) {
            $table->renameColumn('rubrique_montant_prevu', 'rubrique_montant');
        });
    }

    public function down(): void
    {
        Schema::table('rubriques', function (Blueprint $table) {
            $table->renameColumn('rubrique_montant', 'rubrique_montant_prevu');
        });
    }
};
