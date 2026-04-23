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
        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convention_id')->constrained('conventions');
            $table->unsignedBigInteger('montant');
            $table->date('date_reception');
            $table->string('type')->default('tranche'); // avance | tranche
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('convention_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versements');
    }
};
