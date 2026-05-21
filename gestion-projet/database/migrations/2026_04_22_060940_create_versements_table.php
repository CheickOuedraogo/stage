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
        Schema::create('versements', function (Blueprint $table) {
            $table->id('id_versement');
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention');
            $table->bigInteger('versement_montant');
            $table->date('versement_date_reception');
            $table->string('versement_type')->default('tranche'); // avance, tranche, solde
            $table->text('versement_description')->nullable();
            $table->string('versement_reference')->nullable();
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
        Schema::dropIfExists('versements');
    }
};
