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
        Schema::create('bailleurs', function (Blueprint $table) {
            $table->id('id_bailleur');
            $table->string('bailleur_nom');
            $table->string('bailleur_sigle')->nullable();
            $table->string('bailleur_type')->nullable();
            $table->string('bailleur_pays')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailleurs');
    }
};
