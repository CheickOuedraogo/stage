<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projets', function (Blueprint $table): void {
            $table->string('statut_final')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table): void {
            $table->dropColumn('statut_final');
        });
    }
};
