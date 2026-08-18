<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_documentacion', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('fecha_aprobacion');
        });
    }

    public function down(): void
    {
        Schema::table('control_documentacion', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
