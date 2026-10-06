<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('configuracion_institucion', function (Blueprint $table) {
            $table->string('horario_atencion')->nullable()->after('email_contacto');
            $table->unsignedSmallInteger('landing_anios_formacion')->nullable()->after('horario_atencion');
            $table->unsignedSmallInteger('landing_docentes_cantidad')->nullable()->after('landing_anios_formacion');
            $table->unsignedSmallInteger('landing_alumnos_cantidad')->nullable()->after('landing_docentes_cantidad');
            $table->unsignedSmallInteger('landing_egresados_cantidad')->nullable()->after('landing_alumnos_cantidad');
        });
    }

    public function down()
    {
        Schema::table('configuracion_institucion', function (Blueprint $table) {
            $table->dropColumn([
                'horario_atencion',
                'landing_anios_formacion',
                'landing_docentes_cantidad',
                'landing_alumnos_cantidad',
                'landing_egresados_cantidad',
            ]);
        });
    }
};
