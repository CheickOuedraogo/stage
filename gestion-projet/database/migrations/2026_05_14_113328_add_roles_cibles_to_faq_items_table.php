<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faq_items', function (Blueprint $table): void {
            $table->json('roles_cibles')->nullable()->after('faq_actif');
        });
    }

    public function down(): void
    {
        Schema::table('faq_items', function (Blueprint $table): void {
            $table->dropColumn('roles_cibles');
        });
    }
};
