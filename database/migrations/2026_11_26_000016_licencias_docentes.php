<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Inasistencias como licencias (estilo planilla docente):
 *  - tipo_licencia: catálogo (artículo, tope de días por año y por mes, con o sin goce).
 *  - licencia_docente: el trámite (docente, desde/hasta, tipo, documentación, comprobante).
 *  - inasistencia_docente: cada clase que pierde (designación, fecha, horas cátedra),
 *    ahora colgada de su licencia. Si la licencia descuenta, esas horas se descuentan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_licencia', function (Blueprint $table) {
            $table->id('id_tipo_licencia');
            $table->string('nombre', 80)->unique();
            $table->string('articulo', 30)->nullable();
            $table->unsignedSmallInteger('tope_anual')->nullable();   // días por año
            $table->unsignedSmallInteger('tope_mensual')->nullable(); // días por mes
            $table->boolean('con_goce')->default(true);               // sin goce = se descuenta
            $table->timestamps();
        });

        $ahora = now();
        DB::table('tipo_licencia')->insert(array_map(fn ($t) => array_combine(['nombre', 'tope_anual', 'tope_mensual', 'con_goce'], $t) + ['created_at' => $ahora, 'updated_at' => $ahora], [
            ['Inasistencia por razones particulares', 6, 2, true],
            ['Enfermedad de corto tratamiento', null, null, true],
            ['Atención de familiar enfermo', 20, null, true],
            ['Fallecimiento de familiar', null, null, true],
            ['Examen o estudio', 12, null, true],
            ['Matrimonio', 10, null, true],
            ['Otra licencia justificada', null, null, true],
            ['Inasistencia injustificada', null, null, false],
            ['Licencia sin goce de sueldo', null, null, false],
        ]));

        Schema::create('licencia_docente', function (Blueprint $table) {
            $table->id('id_licencia');
            $table->unsignedBigInteger('id_profesor');
            $table->unsignedBigInteger('id_tipo_licencia');
            $table->date('desde');
            $table->date('hasta');
            $table->enum('documentacion', ['Presentada', 'Pendiente', 'No corresponde']);
            $table->date('fecha_presentacion')->nullable();
            $table->string('comprobante')->nullable(); // archivo privado (storage/app)
            $table->string('observaciones', 255)->nullable();
            $table->unsignedBigInteger('id_persona_registra')->nullable();
            $table->timestamps();

            $table->foreign('id_tipo_licencia')->references('id_tipo_licencia')->on('tipo_licencia');
            $table->foreign('id_profesor')->references('id_profesor')->on('profesor')->cascadeOnDelete();
        });

        Schema::table('inasistencia_docente', function (Blueprint $table) {
            $table->unsignedBigInteger('id_licencia')->nullable()->after('id_inasistencia');
        });

        // Las faltas que ya estaban cargadas pasan a ser una licencia cada una.
        $tipos = DB::table('tipo_licencia')->pluck('id_tipo_licencia', 'nombre');
        foreach (DB::table('inasistencia_docente')->get() as $falta) {
            $idLicencia = DB::table('licencia_docente')->insertGetId([
                'id_profesor' => DB::table('asignacion_profesor_materia')->where('id_asignacion', $falta->id_asignacion)->value('id_profesor'),
                'id_tipo_licencia' => $tipos[$falta->justificada ? 'Otra licencia justificada' : 'Inasistencia injustificada'],
                'desde' => $falta->fecha,
                'hasta' => $falta->fecha,
                'documentacion' => $falta->justificada ? 'Presentada' : 'No corresponde',
                'observaciones' => $falta->motivo,
                'id_persona_registra' => $falta->id_persona_registra,
                'created_at' => $falta->created_at,
                'updated_at' => $ahora,
            ]);
            DB::table('inasistencia_docente')->where('id_inasistencia', $falta->id_inasistencia)->update(['id_licencia' => $idLicencia]);
        }

        Schema::table('inasistencia_docente', function (Blueprint $table) {
            $table->dropColumn(['justificada', 'motivo', 'id_persona_registra']);
            $table->foreign('id_licencia')->references('id_licencia')->on('licencia_docente')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inasistencia_docente', function (Blueprint $table) {
            $table->dropForeign(['id_licencia']);
            $table->dropColumn('id_licencia');
            $table->boolean('justificada')->default(false);
            $table->string('motivo', 255)->nullable();
            $table->unsignedBigInteger('id_persona_registra')->nullable();
        });
        Schema::dropIfExists('licencia_docente');
        Schema::dropIfExists('tipo_licencia');
    }
};
