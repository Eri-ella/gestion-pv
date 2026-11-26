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
        Schema::create('pvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->enum('statut', ['brouillon', 'valide', 'archive'])->default('brouillon');
            $table->date('date_deliberation')->nullable();
            $table->string('president_jury')->nullable();
            $table->string('fichier_path')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_validation')->nullable();
            $table->timestamps();
            $table->unique('promotion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pvs');
    }
};
