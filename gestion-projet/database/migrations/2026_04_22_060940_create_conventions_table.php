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
            $table->id();
            $table->foreignId('projet_id')->constrained('projets');
            $table->foreignId('bailleur_id')->constrained('bailleurs');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('montant');
            $table->string('forme')->default('don'); // don | prêt
            $table->string('devise_origine')->default('XOF');
            $table->decimal('taux_conversion', 12, 6)->default(1);
            $table->unsignedBigInteger('montant_fcfa');
            $table->string('status')->default('active');
            $table->date('date_signature')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();

            $table->index('projet_id');
            $table->index('bailleur_id');
            $table->index('status');
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
