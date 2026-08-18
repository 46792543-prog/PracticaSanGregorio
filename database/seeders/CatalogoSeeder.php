<?php

namespace Database\Seeders;

use App\Models\AnioCursada;
use App\Models\AnioLectivo;
use App\Models\CondicionAlumno;
use App\Models\EspecialidadProfesor;
use App\Models\EstadoAnioLectivo;
use App\Models\EstadoCarrera;
use App\Models\EstadoDocumento;
use App\Models\EstadoInscripcion;
use App\Models\EstadoMesa;
use App\Models\EstadoUsuario;
use App\Models\LlamadoExamen;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\NombreMateria;
use App\Models\PeriodoDictado;
use App\Models\RegimenAprobacion;
use App\Models\Rol;
use App\Models\RolTribunal;
use App\Models\TipoAccion;
use App\Models\TipoActa;
use App\Models\TipoCorrelativa;
use App\Models\TipoMovimiento;
use App\Models\TurnoCursada;
use App\Models\TurnoExamen;
use Illuminate\Database\Seeder;

/**
 * Carga solo el "vocabulario del sistema": los catálogos que antes eran
 * ENUM de MySQL. Son equivalentes a lo que ya daban los enums, no son datos
 * de negocio — por eso se seedean siempre, a diferencia de carreras/alumnos/etc.
 * que el usuario carga desde la app. Los ids se insertan en el orden que
 * necesitan los DEFAULT de las migraciones (ej. estado_mesa #1 = Programada).
 */
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Alumno', 'Secretario', 'Director'] as $nombre) {
            Rol::firstOrCreate(['nombre_rol' => $nombre]);
        }

        foreach (['Activo', 'Inactivo'] as $nombre) {
            EstadoUsuario::firstOrCreate(['nombre_estado' => $nombre]);
        }

        foreach (['Activa', 'Inactiva', 'En espera'] as $nombre) {
            EstadoCarrera::firstOrCreate(['nombre_estado' => $nombre]);
        }

        foreach (['Primer Año', 'Segundo Año', 'Tercer Año'] as $nombre) {
            AnioCursada::firstOrCreate(['nombre_anio' => $nombre]);
        }

        foreach (['Anual', '1er Cuatrimestre', '2do Cuatrimestre'] as $nombre) {
            PeriodoDictado::firstOrCreate(['nombre_periodo' => $nombre]);
        }

        foreach (['Solo Promoción', 'Solo Examen Final', 'Promoción o Examen Final'] as $nombre) {
            RegimenAprobacion::firstOrCreate(['nombre_regimen' => $nombre]);
        }

        foreach ([
            'Fundamentos de Enfermería Básica y Comunitaria',
            'Anatomía y Fisiología Humana',
            'Microbiología y Parasitología',
            'Expresión Oral y Escrita',
            'Física y Química Aplicada a la Enfermería',
            'Antropología Filosófica y Socio Cultural',
            'Informática Básica',
            'Práctica Profesionalizante I',
            'Enfermería Médica y Especialidades',
            'Ética y Marco Legal en la Práctica de Enfermería',
            'Farmacología',
            'Nutrición',
            'Enfermería Quirúrgica y Especialidades',
            'Psicología',
            'Introducción a la Metodología de la Investigación',
            'EDI I',
            'Práctica Profesionalizante II',
            'Enfermería Materno Infanto Juvenil',
            'Informática Aplicada a la Enfermería',
            'Inglés Técnico',
            'Organización y Gestión de Enfermería',
            'Enfermería en Salud Mental',
            'EDI II',
            'Práctica Profesionalizante III',
        ] as $nombre) {
            NombreMateria::firstOrCreate(['nombre' => $nombre]);
        }

        foreach (['Regulariza para cursar', 'Aprobadas para rendir'] as $nombre) {
            TipoCorrelativa::firstOrCreate(['nombre_tipo' => $nombre]);
        }

        foreach (['Pendiente', 'Entregado', 'Aprobado', 'Rechazado'] as $nombre) {
            EstadoDocumento::firstOrCreate(['nombre_estado' => $nombre]);
        }

        foreach (['Activo', 'Cerrado'] as $nombre) {
            EstadoAnioLectivo::firstOrCreate(['nombre_estado' => $nombre]);
        }

        // Tabla compartida por inscripcion_carrera (Activo/Baja) e inscripcion_mesa
        // (En proceso/Aceptado/Rechazado) en el modelo del dump. #1 = En proceso
        // porque inscripcion_mesa usa ese id como DEFAULT.
        foreach (['En proceso', 'Aceptado', 'Rechazado', 'Activo', 'Baja'] as $nombre) {
            EstadoInscripcion::firstOrCreate(['nombre_estado' => $nombre]);
        }

        foreach (['Mañana', 'Tarde', 'Noche'] as $nombre) {
            TurnoCursada::firstOrCreate(['nombre_turno' => $nombre]);
        }

        // Tabla compartida por inscripcion_carrera (Regular/Promoción/Libre) e
        // historial_alumno (Pendiente/Cursando/Regular/Aprobada) en el dump.
        foreach (['Regular', 'Promoción', 'Libre', 'Pendiente', 'Cursando', 'Aprobada'] as $nombre) {
            CondicionAlumno::firstOrCreate(['nombre_condicion' => $nombre]);
        }

        foreach ([
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
        ] as $nombre) {
            Mes::firstOrCreate(['nombre_mes' => $nombre]);
        }

        // #1 = Programada porque mesa_examen usa ese id como DEFAULT.
        foreach (['Programada', 'Finalizada', 'Cancelada'] as $nombre) {
            EstadoMesa::firstOrCreate(['nombre_estado' => $nombre]);
        }

        foreach (['Turno Febrero/Marzo', 'Turno Julio', 'Turno Noviembre/Diciembre'] as $nombre) {
            TurnoExamen::firstOrCreate(['nombre_turno' => $nombre]);
        }

        foreach (['Primer llamado', 'Segundo llamado'] as $nombre) {
            LlamadoExamen::firstOrCreate(['nombre_llamado' => $nombre]);
        }

        foreach ([
            'Anatomía y Fisiología Humana',
            'Base de Datos',
            'Enfermería en Salud Mental',
            'Enfermería Materno Infanto Juvenil',
            'Enfermería Médica y Especialidades',
            'Enfermería Quirúrgica',
            'Farmacología',
            'Microbiología y Parasitología',
            'Programación',
        ] as $nombre) {
            EspecialidadProfesor::firstOrCreate(['nombre_especialidad' => $nombre]);
        }

        foreach (['Presidente', 'Vocal 1', 'Vocal 2'] as $nombre) {
            RolTribunal::firstOrCreate(['nombre_rol' => $nombre]);
        }

        foreach (['Acta de Examen Final', 'Acta de Promoción', 'Acta de Mesa Especial'] as $nombre) {
            TipoActa::firstOrCreate(['nombre_tipo' => $nombre]);
        }

        foreach (['Ingreso', 'Egreso'] as $nombre) {
            TipoMovimiento::firstOrCreate(['nombre_tipo' => $nombre]);
        }

        // #1 = Efectivo porque movimiento_caja usa ese id como DEFAULT.
        foreach (['Efectivo', 'Transferencia'] as $nombre) {
            MedioPago::firstOrCreate(['nombre_medio' => $nombre]);
        }

        foreach (['Alta Cargo', 'Baja Definitiva', 'Comisión de Servicio', 'Licencia Médica'] as $nombre) {
            TipoAccion::firstOrCreate(['nombre_tipo' => $nombre]);
        }

        // institucion_origen y concepto_caja quedan vacías a propósito (igual
        // que en el dump): se completan al vuelo desde los formularios que las usan.

        // El año lectivo activo es bootstrap indispensable (como el usuario
        // semilla): sin al menos uno, no se puede inscribir a ningún alumno,
        // y la app no tiene pantalla para crearlo (tampoco la tenía antes).
        AnioLectivo::firstOrCreate(
            ['anio' => now()->year],
            ['id_estado_anio' => EstadoAnioLectivo::where('nombre_estado', 'Activo')->value('id_estado_anio')]
        );
    }
}
