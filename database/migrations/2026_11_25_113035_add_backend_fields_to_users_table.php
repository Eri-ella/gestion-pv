<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
            $table->foreignId('departement_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
            $table->boolean('actif')->default(true)->after('departement_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departement_id');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('actif');
        });
    }
};
