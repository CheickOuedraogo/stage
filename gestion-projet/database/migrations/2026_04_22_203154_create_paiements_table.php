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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->unique()->constrained('demandes_depenses')->cascadeOnDelete();
            $table->integer('montant');
            $table->date('date_paiement');
            $table->string('mode_paiement');
            $table->string('reference')->nullable();
            $table->foreignId('enregistre_par')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
