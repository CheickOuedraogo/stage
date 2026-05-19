<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('utilisateur_role')->default('porteur')->after('email');
            $table->boolean('utilisateur_actif')->default(true)->after('utilisateur_role');
            $table->string('avatar_path')->nullable()->after('utilisateur_actif');
            $table->string('telephone', 20)->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['utilisateur_role', 'utilisateur_actif', 'avatar_path', 'telephone']);
        });
    }
};
