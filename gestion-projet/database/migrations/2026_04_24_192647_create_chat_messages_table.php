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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id('id_message');
            $table->foreignId('id_expediteur')->constrained('users', 'id_utilisateur')->cascadeOnDelete();
            $table->foreignId('id_destinataire')->constrained('users', 'id_utilisateur')->cascadeOnDelete();
            $table->text('message_contenu');
            $table->boolean('message_lu')->default(false);
            $table->timestamps();

            $table->index(['id_expediteur', 'id_destinataire']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
