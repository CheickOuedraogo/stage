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
        Schema::create('projets', function (Blueprint $table) {
            $table->id('id_projet');
            $table->foreignId('id_porteur')->constrained('utilisateurs', 'id_utilisateur');
            $table->string('projet_titre');
            $table->text('projet_description')->nullable();
            $table->text('projet_objectifs')->nullable();
            $table->text('projet_activites')->nullable();
            $table->bigInteger('projet_montant_estime');
            $table->string('projet_statut')->default('en_attente_financement')->index();
            $table->string('statut_final')->nullable();
            $table->date('projet_date_debut')->nullable();
            $table->date('projet_date_fin_prevue')->nullable();
            $table->date('projet_date_fin_reelle')->nullable();
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();

            $table->index('id_porteur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
