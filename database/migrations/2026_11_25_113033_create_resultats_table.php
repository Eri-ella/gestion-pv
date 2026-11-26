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
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pv_id')->constrained()->cascadeOnDelete();
            $table->foreignId('etudiant_id')->constrained()->cascadeOnDelete();
            $table->decimal('moyenne', 5, 2)->nullable();
            $table->decimal('credits_valides', 5, 2)->default(0);
            $table->enum('decision', ['admis', 'ajourne', 'rattrapage'])->nullable();
            $table->timestamps();
            $table->unique(['pv_id', 'etudiant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
