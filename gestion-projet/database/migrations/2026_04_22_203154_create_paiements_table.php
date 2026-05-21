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
            $table->foreignId('id_demande')->unique()->constrained('demandes_depense', 'id_demande')->onDelete('cascade');
            $table->integer('paiement_montant');
            $table->date('paiement_date');
            $table->string('paiement_mode'); // virement, chèque, espèces
            $table->string('paiement_reference')->nullable();
            $table->foreignId('id_enregistreur_paiement')->constrained('utilisateurs', 'id_utilisateur')->onDelete('cascade');
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();
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
