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
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->foreignId('id_utilisateur')->constrained('utilisateurs', 'id_utilisateur')->cascadeOnDelete();
            $table->string('type_notification', 50);
            $table->foreignId('id_demande')->nullable()->constrained('demandes_depense', 'id_demande')->nullOnDelete();
            $table->foreignId('id_projet')->nullable()->constrained('projets', 'id_projet')->nullOnDelete();
            $table->string('notification_objet')->nullable();
            $table->string('notification_libelle_statut', 100)->nullable();
            $table->text('notification_motif')->nullable();
            $table->timestamp('lu_le')->nullable();
            $table->timestamp('cree_le')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
};
