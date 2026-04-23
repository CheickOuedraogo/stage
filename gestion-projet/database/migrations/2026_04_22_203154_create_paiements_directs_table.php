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
            $table->id();
            $table->foreignId('convention_id')->constrained('conventions')->cascadeOnDelete();
            $table->foreignId('rubrique_id')->nullable()->constrained('rubriques')->nullOnDelete();
            $table->integer('montant');
            $table->string('objet_depense');
            $table->text('description')->nullable();
            $table->date('date_paiement');
            $table->foreignId('enregistre_par')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('convention_id');
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
