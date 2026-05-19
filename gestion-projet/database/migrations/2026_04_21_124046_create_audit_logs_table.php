<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('id_audit');
            $table->foreignId('id_utilisateur')->nullable()->constrained('users', 'id_utilisateur')->nullOnDelete();
            $table->string('audit_action'); // created | updated | deleted | login | logout | custom
            $table->string('audit_entite_type')->nullable();
            $table->unsignedBigInteger('audit_entite_id')->nullable();
            $table->json('audit_anciennes_valeurs')->nullable();
            $table->json('audit_nouvelles_valeurs')->nullable();
            $table->string('audit_adresse_ip', 45)->nullable();
            $table->text('audit_navigateur')->nullable();
            $table->string('audit_description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['audit_entite_type', 'audit_entite_id']);
            $table->index('id_utilisateur');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
