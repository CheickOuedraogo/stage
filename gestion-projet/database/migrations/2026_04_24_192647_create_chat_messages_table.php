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
        Schema::create('messages_chat', function (Blueprint $table) {
            $table->id('id_message');
            $table->foreignId('id_expediteur')->constrained('utilisateurs', 'id_utilisateur')->onDelete('cascade');
            $table->foreignId('id_destinataire')->constrained('utilisateurs', 'id_utilisateur')->onDelete('cascade');
            $table->text('message_contenu');
            $table->boolean('message_lu')->default(false);
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();

            $table->index(['id_expediteur', 'id_destinataire']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages_chat');
    }
};
