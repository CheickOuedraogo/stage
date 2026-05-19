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
        Schema::create('conventions', function (Blueprint $table) {
            $table->id('id_convention');
            $table->foreignId('id_projet')->constrained('projets', 'id_projet');
            $table->foreignId('id_bailleur')->constrained('bailleurs', 'id_bailleur');
            $table->string('convention_titre');
            $table->text('convention_description')->nullable();
            $table->unsignedBigInteger('convention_montant');
            $table->string('convention_forme')->default('don'); // don | prêt
            $table->string('convention_devise')->default('XOF');
            $table->decimal('convention_taux_conversion', 12, 6)->default(1);
            $table->unsignedBigInteger('convention_montant_fcfa');
            $table->string('convention_statut')->default('active');
            $table->date('convention_date_signature')->nullable();
            $table->date('convention_date_debut')->nullable();
            $table->date('convention_date_fin')->nullable();
            $table->timestamps();

            $table->index('id_projet');
            $table->index('id_bailleur');
            $table->index('convention_statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conventions');
    }
};
