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
        Schema::create('paiements_directs', function (Blueprint $table) {
            $table->id('id_paiement_direct');
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention')->cascadeOnDelete();
            $table->foreignId('id_rubrique')->nullable()->constrained('rubriques', 'id_rubrique')->nullOnDelete();
            $table->integer('paiement_direct_montant');
            $table->string('paiement_direct_objet');
            $table->text('paiement_direct_description')->nullable();
            $table->date('paiement_direct_date');
            $table->foreignId('id_enregistreur_paiement_direct')->constrained('users', 'id_utilisateur')->cascadeOnDelete();
            $table->timestamps();

            $table->index('id_convention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements_directs');
    }
};
