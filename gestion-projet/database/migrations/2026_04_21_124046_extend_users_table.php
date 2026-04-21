<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('porteur')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('avatar_path')->nullable()->after('is_active');
            $table->string('telephone', 20)->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'avatar_path', 'telephone']);
        });
    }
};
