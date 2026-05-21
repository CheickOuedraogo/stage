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
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('parametre_cle')->unique();
            $table->text('parametre_valeur')->nullable();
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
