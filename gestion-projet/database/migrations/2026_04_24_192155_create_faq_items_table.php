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
        Schema::create('faq', function (Blueprint $table) {
            $table->id('id_faq');
            $table->string('faq_question');
            $table->text('faq_reponse');
            $table->boolean('faq_actif')->default(true);
            $table->boolean('visible_porteur')->default(true);
            $table->boolean('visible_daf')->default(true);
            $table->boolean('visible_ac')->default(true);
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq');
    }
};
