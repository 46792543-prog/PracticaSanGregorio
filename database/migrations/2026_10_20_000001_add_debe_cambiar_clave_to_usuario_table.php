<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca a los usuarios que todavía tienen la clave inicial generada por la
 * secretaría: al entrar por primera vez se les pide que la cambien.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->boolean('debe_cambiar_clave')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('debe_cambiar_clave');
        });
    }
};
