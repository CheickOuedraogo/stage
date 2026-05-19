<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_depenses', function (Blueprint $table) {
            $table->id('id_demande');
            $table->foreignId('id_rubrique')->constrained('rubriques', 'id_rubrique')->cascadeOnDelete();
            $table->foreignId('id_convention')->constrained('conventions', 'id_convention')->cascadeOnDelete();
            $table->foreignId('id_porteur')->constrained('users', 'id_utilisateur')->cascadeOnDelete();
            $table->integer('demande_montant');
            $table->string('demande_objet');
            $table->text('demande_description')->nullable();
            $table->string('demande_justificatif')->nullable();
            $table->string('demande_statut')->default('soumise');
            $table->text('demande_motif_rejet')->nullable();
            $table->string('demande_rapport')->nullable();
            $table->timestamp('demande_date_validation_daf')->nullable();
            $table->foreignId('id_validateur_daf')->nullable()->constrained('users', 'id_utilisateur')->nullOnDelete();
            $table->timestamp('demande_date_validation_ac')->nullable();
            $table->foreignId('id_validateur_ac')->nullable()->constrained('users', 'id_utilisateur')->nullOnDelete();
            $table->boolean('demande_rapport_valide_daf')->default(false);
            $table->boolean('demande_rapport_valide_ac')->default(false);
            $table->timestamps();

            $table->index(['id_convention', 'demande_statut']);
            $table->index(['id_porteur', 'demande_statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_depenses');
    }
};
