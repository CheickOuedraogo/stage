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
            $table->string('bailleur_type')->nullable(); // multilatéral, bilatéral, fondation, etc.
            $table->string('bailleur_pays')->nullable();
            $table->string('bailleur_contact')->nullable();
            $table->string('bailleur_email')->nullable();
            $table->string('bailleur_telephone')->nullable();
            $table->string('bailleur_adresse')->nullable();
            $table->text('bailleur_description')->nullable();
            $table->timestamp('cree_le')->useCurrent();
            $table->timestamp('mis_a_jour_le')->useCurrent();
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
