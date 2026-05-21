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
        Schema::create('rubriques', function (Blueprint $table) {
            $table->id('id_rubrique');
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention');
            $table->string('rubrique_libelle');
            $table->bigInteger('rubrique_montant_prevu');
            $table->text('rubrique_description')->nullable();
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();

            $table->index('id_convention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubriques');
    }
};
