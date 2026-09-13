<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materia', function (Blueprint $table) {
            $table->boolean('activa')->default(true)->after('version_plan');
        });
    }

    public function down(): void
    {
        Schema::table('materia', function (Blueprint $table) {
            $table->dropColumn('activa');
        });
    }
};
