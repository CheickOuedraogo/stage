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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('utilisateur_nom');
            $table->string('utilisateur_email')->unique();
            $table->timestamp('email_verifie_le')->nullable();
            $table->string('utilisateur_mot_de_passe');
            $table->rememberToken()->comment('jeton_souvenir'); // Laravel expects remember_token name for some internal logic, but we map it in model
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();

            // From extend migration
            $table->string('utilisateur_role')->default('porteur');
            $table->boolean('utilisateur_actif')->default(true);
            $table->string('utilisateur_avatar_chemin')->nullable();
            $table->string('utilisateur_telephone', 20)->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
