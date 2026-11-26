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
        Schema::create('pv_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pv_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('fonction')->nullable();
            $table->boolean('president')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pv_membres');
    }
};
