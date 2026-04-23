<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrique_id')->constrained('rubriques')->cascadeOnDelete();
            $table->foreignId('convention_id')->constrained('conventions')->cascadeOnDelete();
            $table->foreignId('porteur_id')->constrained('users')->cascadeOnDelete();
            $table->integer('montant');
            $table->string('objet');
            $table->text('description')->nullable();
            $table->string('justificatif_path')->nullable();
            $table->string('status')->default('soumise');
            $table->text('motif_rejet')->nullable();
            $table->string('rapport_path')->nullable();
            $table->timestamp('validee_daf_at')->nullable();
            $table->foreignId('validee_daf_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validee_ac_at')->nullable();
            $table->foreignId('validee_ac_par')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('rapport_validee_daf')->default(false);
            $table->boolean('rapport_validee_ac')->default(false);
            $table->timestamps();

            $table->index(['convention_id', 'status']);
            $table->index(['porteur_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_depenses');
    }
};
