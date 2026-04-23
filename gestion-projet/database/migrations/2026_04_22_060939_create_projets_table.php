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
            $table->id();
            $table->foreignId('porteur_id')->constrained('users');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->text('objectifs')->nullable();
            $table->text('activites')->nullable();
            $table->unsignedBigInteger('montant_estime');
            $table->string('status')->default('en_attente_financement');
            $table->date('date_debut')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->date('date_fin_reelle')->nullable();
            $table->timestamps();

            $table->index('porteur_id');
            $table->index('status');
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
