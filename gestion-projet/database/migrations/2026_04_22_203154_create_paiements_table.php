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
            $table->id('id_paiement');
            $table->foreignId('id_demande')->unique()->constrained('demandes_depenses', 'id_demande')->cascadeOnDelete();
            $table->integer('paiement_montant');
            $table->date('paiement_date');
            $table->string('paiement_mode');
            $table->string('paiement_reference')->nullable();
            $table->foreignId('id_enregistreur_paiement')->constrained('users', 'id_utilisateur')->cascadeOnDelete();
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
