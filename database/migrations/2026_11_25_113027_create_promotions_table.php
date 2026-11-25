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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annee_universitaire_id')->constrained()->restrictOnDelete();
            $table->foreignId('filiere_id')->constrained()->restrictOnDelete();
            $table->foreignId('niveau_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['annee_universitaire_id', 'filiere_id', 'niveau_id'], 'promotions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
