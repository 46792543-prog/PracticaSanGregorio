<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acta', function (Blueprint $table) {
            $table->enum('estado', ['borrador', 'generada'])->default('borrador')->after('id_tipo_acta');
            $table->text('observaciones')->nullable()->after('id_director_firmante');
        });
    }

    public function down(): void
    {
        Schema::table('acta', function (Blueprint $table) {
            $table->dropColumn(['estado', 'observaciones']);
        });
    }
};
