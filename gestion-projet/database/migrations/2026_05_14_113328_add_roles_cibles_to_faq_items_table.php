<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faq_items', function (Blueprint $table): void {
            $table->boolean('visible_porteur')->default(true)->after('faq_actif');
            $table->boolean('visible_daf')->default(true)->after('visible_porteur');
            $table->boolean('visible_ac')->default(true)->after('visible_daf');
        });
    }

    public function down(): void
    {
        Schema::table('faq_items', function (Blueprint $table): void {
            $table->dropColumn(['visible_porteur', 'visible_daf', 'visible_ac']);
        });
    }
};
