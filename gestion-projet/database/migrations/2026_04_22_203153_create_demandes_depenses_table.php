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
        Schema::create('demandes_depense', function (Blueprint $table) {
            $table->id('id_demande');
            $table->foreignId('id_rubrique')->constrained('rubriques', 'id_rubrique')->onDelete('cascade');
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention')->onDelete('cascade');
            $table->foreignId('id_porteur')->constrained('utilisateurs', 'id_utilisateur')->onDelete('cascade');
            $table->integer('demande_montant');
            $table->string('demande_objet');
            $table->text('demande_description')->nullable();
            $table->string('demande_justificatif')->nullable();
            $table->string('demande_statut')->default('soumise');
            $table->text('demande_motif_rejet')->nullable();
            $table->string('demande_rapport')->nullable();

            // Circuit validation
            $table->timestamp('demande_date_validation_daf')->nullable();
            $table->foreignId('id_validateur_daf')->nullable()->constrained('utilisateurs', 'id_utilisateur')->onDelete('set null');

            $table->timestamp('demande_date_validation_ac')->nullable();
            $table->foreignId('id_validateur_ac')->nullable()->constrained('utilisateurs', 'id_utilisateur')->onDelete('set null');

            $table->boolean('demande_rapport_valide_daf')->default(false);
            $table->boolean('demande_rapport_valide_ac')->default(false);

            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();

            $table->index(['id_convention', 'demande_statut']);
            $table->index(['id_porteur', 'demande_statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_depense');
    }
};
