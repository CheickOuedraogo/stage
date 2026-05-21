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
        Schema::create('journaux_audit', function (Blueprint $table) {
            $table->id('id_audit');
            $table->foreignId('id_utilisateur')->nullable()->constrained('utilisateurs', 'id_utilisateur')->onDelete('set null');
            $table->string('audit_action');
            $table->string('audit_entite_type')->nullable();
            $table->bigInteger('audit_entite_id')->nullable();
            $table->json('audit_anciennes_valeurs')->nullable();
            $table->json('audit_nouvelles_valeurs')->nullable();
            $table->string('audit_adresse_ip', 45)->nullable();
            $table->text('audit_navigateur')->nullable();
            $table->string('audit_description')->nullable();
            $table->timestamp('cree_le')->useCurrent()->index();

            $table->index(['audit_entite_type', 'audit_entite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journaux_audit');
    }
};
