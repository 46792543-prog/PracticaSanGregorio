<?php

namespace App\Support;

use App\Models\Auditoria;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Traduce lo que guarda el Auditor (nombres de tablas, columnas e ids) a
 * texto legible para la pantalla de Auditoría: "usuario" → "Usuario",
 * "id_estado: 1 → 2" → "Estado: Activo → Inactivo", y agrega de quién es el
 * registro ("… de Pérez, Juan"). Se calcula al mostrar, así que también
 * sirve para los registros que ya estaban guardados.
 */
class AuditoriaFormato
{
    /** tabla => [artículo, nombre legible] */
    private const TABLAS = [
        'persona' => ['la', 'persona'],
        'usuario' => ['el', 'usuario'],
        'rol' => ['el', 'rol'],
        'carrera' => ['la', 'carrera'],
        'materia' => ['la', 'materia'],
        'nombre_materia' => ['el', 'nombre de materia'],
        'correlativa' => ['la', 'correlativa'],
        'profesor' => ['el', 'profesor'],
        'especialidad_profesor' => ['la', 'especialidad'],
        'asignacion_profesor_materia' => ['la', 'asignación de profesor'],
        'horario_asignacion' => ['el', 'horario de asignación'],
        'anio_lectivo' => ['el', 'año lectivo'],
        'inscripcion_carrera' => ['la', 'inscripción a carrera'],
        'historial_alumno' => ['el', 'historial académico'],
        'seguimiento_alumno' => ['la', 'nota de seguimiento'],
        'control_documentacion' => ['la', 'documentación'],
        'documento_requisito' => ['el', 'requisito de documentación'],
        'cuota_alumno' => ['la', 'cuota'],
        'movimiento_caja' => ['el', 'movimiento de caja'],
        'concepto_caja' => ['el', 'concepto de caja'],
        'mesa_examen' => ['la', 'mesa de examen'],
        'tribunal_mesa' => ['el', 'tribunal de mesa'],
        'turno_examen' => ['el', 'turno de examen'],
        'llamado_examen' => ['el', 'llamado de examen'],
        'inscripcion_mesa' => ['la', 'inscripción a mesa'],
        'acta' => ['el', 'acta'],
        'detalle_acta' => ['la', 'nota del acta'],
        'equivalencia' => ['la', 'equivalencia'],
        'configuracion_institucion' => ['la', 'configuración institucional'],
        'imagen_landing' => ['la', 'imagen de la landing'],
        'pago_docente' => ['el', 'pago a docente'],
        'metodo_pago_docente' => ['el', 'método de pago docente'],
        'estado_pago_docente' => ['el', 'estado de pago docente'],
        'periodo_inscripcion_cursada' => ['el', 'período de inscripción a cursada'],
        'horario_modulo' => ['el', 'módulo de horario'],
        'horario_celda' => ['la', 'celda de horario'],
        'ficha_seccion' => ['la', 'sección de la ficha'],
        'ficha_campo' => ['el', 'campo de la ficha'],
        'ficha_campo_opcion' => ['la', 'opción de campo'],
        'ficha_respuesta' => ['la', 'respuesta de la ficha'],
    ];

    /** columna FK => [tabla, clave primaria] (las que apuntan a persona se muestran como "Apellido, Nombre") */
    private const CLAVES_FORANEAS = [
        'id_acta' => ['acta', 'id_acta'],
        'id_anio_cursada' => ['anio_cursada', 'id_anio_cursada'],
        'id_anio_lectivo' => ['anio_lectivo', 'id_anio_lectivo'],
        'id_campo' => ['ficha_campo', 'id_campo'],
        'id_carrera' => ['carrera', 'id_carrera'],
        'id_concepto' => ['concepto_caja', 'id_concepto'],
        'id_condicion' => ['condicion_alumno', 'id_condicion'],
        'id_especialidad' => ['especialidad_profesor', 'id_especialidad'],
        'id_estado' => ['estado_usuario', 'id_estado'],
        'id_estado_anio' => ['estado_anio_lectivo', 'id_estado_anio'],
        'id_estado_carrera' => ['estado_carrera', 'id_estado_carrera'],
        'id_estado_documento' => ['estado_documento', 'id_estado_documento'],
        'id_estado_inscripcion' => ['estado_inscripcion', 'id_estado_inscripcion'],
        'id_estado_mesa' => ['estado_mesa', 'id_estado_mesa'],
        'id_estado_pago_docente' => ['estado_pago_docente', 'id_estado_pago_docente'],
        'id_institucion_origen' => ['institucion_origen', 'id_institucion'],
        'id_llamado' => ['llamado_examen', 'id_llamado'],
        'id_materia' => ['materia', 'id_materia'],
        'id_materia_destino' => ['materia', 'id_materia'],
        'id_materia_principal' => ['materia', 'id_materia'],
        'id_materia_requisito' => ['materia', 'id_materia'],
        'id_medio_pago' => ['medio_pago', 'id_medio_pago'],
        'id_mes' => ['mes', 'id_mes'],
        'id_mesa' => ['mesa_examen', 'id_mesa'],
        'id_metodo_pago_docente' => ['metodo_pago_docente', 'id_metodo_pago_docente'],
        'id_nombre_materia' => ['nombre_materia', 'id_nombre_materia'],
        'id_periodo' => ['periodo_dictado', 'id_periodo'],
        'id_profesor' => ['profesor', 'id_profesor'],
        'id_regimen' => ['regimen_aprobacion', 'id_regimen'],
        'id_requisito' => ['documento_requisito', 'id_requisito'],
        'id_rol' => ['rol', 'id_rol'],
        'id_rol_tribunal' => ['rol_tribunal', 'id_rol_tribunal'],
        'id_seccion' => ['ficha_seccion', 'id_seccion'],
        'id_tipo_acta' => ['tipo_acta', 'id_tipo_acta'],
        'id_tipo_correlativa' => ['tipo_correlativa', 'id_tipo_correlativa'],
        'id_tipo_movimiento' => ['tipo_movimiento', 'id_tipo_movimiento'],
        'id_turno' => ['turno_examen', 'id_turno'],
        'id_turno_cursada' => ['turno_cursada', 'id_turno_cursada'],
        'id_usuario' => ['usuario', 'id_usuario'],
        'id_persona' => ['persona', 'id_persona'],
        'id_persona_alumno' => ['persona', 'id_persona'],
        'id_persona_autor' => ['persona', 'id_persona'],
        'id_secretario_baja' => ['persona', 'id_persona'],
        'id_secretario_creador' => ['persona', 'id_persona'],
        'id_secretario_modifica' => ['persona', 'id_persona'],
        'id_secretario_recibe' => ['persona', 'id_persona'],
        'id_secretario_registra' => ['persona', 'id_persona'],
        'id_secretario_reactiva' => ['persona', 'id_persona'],
        'id_director_firmante' => ['persona', 'id_persona'],
        'id_director_registra' => ['persona', 'id_persona'],
    ];

    /** Columna con el nombre "humano" de cada tabla, para mostrar un id como texto. */
    private const COLUMNA_NOMBRE = [
        'rol' => 'nombre_rol',
        'estado_usuario' => 'nombre_estado',
        'estado_carrera' => 'nombre_estado',
        'anio_cursada' => 'nombre_anio',
        'periodo_dictado' => 'nombre_periodo',
        'regimen_aprobacion' => 'nombre_regimen',
        'nombre_materia' => 'nombre',
        'tipo_correlativa' => 'nombre_tipo',
        'estado_documento' => 'nombre_estado',
        'estado_anio_lectivo' => 'nombre_estado',
        'estado_inscripcion' => 'nombre_estado',
        'turno_cursada' => 'nombre_turno',
        'condicion_alumno' => 'nombre_condicion',
        'mes' => 'nombre_mes',
        'estado_mesa' => 'nombre_estado',
        'turno_examen' => 'nombre_turno',
        'llamado_examen' => 'nombre_llamado',
        'especialidad_profesor' => 'nombre_especialidad',
        'rol_tribunal' => 'nombre_rol',
        'tipo_acta' => 'nombre_tipo',
        'tipo_movimiento' => 'nombre_tipo',
        'institucion_origen' => 'nombre_institucion',
        'medio_pago' => 'nombre_medio',
        'usuario' => 'email',
        'carrera' => 'nombre_carrera',
        'anio_lectivo' => 'anio',
        'concepto_caja' => 'nombre_concepto',
        'configuracion_institucion' => 'nombre_institucion',
        'documento_requisito' => 'nombre_documento',
        'metodo_pago_docente' => 'nombre_metodo',
        'estado_pago_docente' => 'nombre_estado',
        'ficha_seccion' => 'nombre',
        'ficha_campo' => 'etiqueta',
        'ficha_campo_opcion' => 'valor',
    ];

    /** Nombres de columnas que no se deducen bien solos. */
    private const CAMPOS = [
        'id_estado' => 'Estado',
        'id_persona' => 'Persona',
        'id_persona_alumno' => 'Alumno',
        'id_persona_autor' => 'Autor',
        'id_secretario_baja' => 'Dado de baja por',
        'id_secretario_creador' => 'Creado por',
        'id_secretario_modifica' => 'Modificado por',
        'id_secretario_recibe' => 'Recibido por',
        'id_secretario_registra' => 'Registrado por',
        'id_secretario_reactiva' => 'Reactivado por',
        'id_director_firmante' => 'Firmado por',
        'id_director_registra' => 'Registrado por',
        'id_condicion' => 'Condición',
        'id_concepto' => 'Concepto',
        'id_regimen' => 'Régimen',
        'id_periodo' => 'Período',
        'id_requisito' => 'Documento',
        'id_seccion' => 'Sección',
        'id_turno' => 'Turno',
        'id_movimiento_caja' => 'Movimiento de caja',
        'duracion_anos' => 'Duración (años)',
        'anio' => 'Año',
        'id_anio_lectivo' => 'Año lectivo',
        'id_anio_cursada' => 'Año de cursada',
        'anios_plazo_regularidad' => 'Plazo de regularidad (años)',
        'numero_orden' => 'N.º de orden',
        'es_obligatorio' => 'Obligatorio',
        'email' => 'Email',
        'dni' => 'DNI',
        'nota_cursada' => 'Nota de cursada',
        'nota_examen' => 'Nota de examen',
        'cupo_maximo' => 'Cupo máximo',
        'descripcion_detalle' => 'Descripción',
        'logo_path' => 'Logo',
        'path' => 'Archivo',
        'archivo_path' => 'Archivo',
    ];

    private const BOOLEANOS = ['pagado', 'activo', 'activa', 'obligatorio', 'abierto', 'es_obligatorio', 'requiere_regularizada', 'requiere_aprobada'];

    /** Columnas técnicas que no aportan nada al lector. */
    private const OCULTAS = ['remember_token', 'archivo_nombre_original'];

    private static array $cache = [];

    public static function tabla(string $tabla): string
    {
        return Str::ucfirst(self::TABLAS[$tabla][1] ?? str_replace('_', ' ', $tabla));
    }

    public static function descripcion(Auditoria $evento): string
    {
        $verbo = ['creado' => 'Creó', 'actualizado' => 'Actualizó', 'eliminado' => 'Eliminó'][$evento->accion] ?? $evento->accion;
        [$articulo, $nombre] = self::TABLAS[$evento->tabla] ?? ['el registro de', str_replace('_', ' ', $evento->tabla)];

        $texto = "{$verbo} {$articulo} {$nombre}";

        try {
            $sujeto = self::sujeto($evento);
        } catch (Throwable) {
            $sujeto = null;
        }

        if ($sujeto && $sujeto['texto']) {
            $texto .= $sujeto['persona'] ? " de {$sujeto['texto']}" : " «{$sujeto['texto']}»";
        }

        return $texto;
    }

    /** @return array<int, array{campo: string, antes: ?string, despues: ?string, valor: ?string}> */
    public static function cambios(Auditoria $evento): array
    {
        $filas = [];
        $pk = self::clavePrimaria($evento->tabla);

        foreach ((array) $evento->cambios as $campo => $valor) {
            if ($campo === $pk || in_array($campo, self::OCULTAS, true)) {
                continue;
            }

            if (is_array($valor) && array_key_exists('antes', $valor)) {
                $filas[] = [
                    'campo' => self::campo($campo),
                    'antes' => self::valor($campo, $valor['antes']),
                    'despues' => self::valor($campo, $valor['despues']),
                    'valor' => null,
                ];
            } else {
                $filas[] = ['campo' => self::campo($campo), 'antes' => null, 'despues' => null, 'valor' => self::valor($campo, $valor)];
            }
        }

        return $filas;
    }

    public static function campo(string $campo): string
    {
        if (isset(self::CAMPOS[$campo])) {
            return self::CAMPOS[$campo];
        }

        $texto = str_replace('_', ' ', preg_replace('/^id_/', '', $campo));
        $texto = str_replace(['anio', 'inscripcion', 'condicion', 'descripcion', 'direccion', 'periodo', 'aprobacion', 'generacion', 'modificacion', 'reactivacion', 'asignacion', 'telefono', 'atencion', 'formacion'],
            ['año', 'inscripción', 'condición', 'descripción', 'dirección', 'período', 'aprobación', 'generación', 'modificación', 'reactivación', 'asignación', 'teléfono', 'atención', 'formación'], $texto);

        // "fecha baja" → "fecha de baja", "estado inscripción" → "estado de inscripción"
        $texto = preg_replace('/^(fecha|estado|tipo|nombre|turno|período) (?!de )/u', '$1 de ', $texto);

        return Str::ucfirst($texto);
    }

    public static function valor(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        if ($valor === '[protegido]') {
            return '(oculto)';
        }

        if (in_array($campo, self::BOOLEANOS, true)) {
            return $valor ? 'Sí' : 'No';
        }

        if (isset(self::CLAVES_FORANEAS[$campo]) && is_numeric($valor)) {
            [$tabla, $pk] = self::CLAVES_FORANEAS[$campo];

            try {
                return self::nombreDe($tabla, $pk, (int) $valor) ?? "#{$valor}";
            } catch (Throwable) {
                return "#{$valor}";
            }
        }

        if (is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2})?/', $valor, $m)) {
            $fecha = Carbon::parse($valor);

            return isset($m[1]) && $fecha->format('H:i') !== '00:00' ? $fecha->format('d/m/Y H:i') : $fecha->format('d/m/Y');
        }

        if (is_array($valor)) {
            return implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $valor));
        }

        return (string) $valor;
    }

    /** Busca de quién/qué es el registro auditado: primero en los datos guardados, si no en la base. */
    private static function sujeto(Auditoria $evento): ?array
    {
        $datos = [];

        foreach ((array) $evento->cambios as $campo => $valor) {
            $datos[$campo] = is_array($valor) && array_key_exists('despues', $valor) ? $valor['despues'] : $valor;
        }

        $pk = self::clavePrimaria($evento->tabla);

        if ($evento->accion === 'actualizado' && $pk && $evento->id_registro) {
            $fila = self::fila($evento->tabla, $pk, (int) $evento->id_registro);
            if ($fila) {
                $datos = array_merge((array) $fila, array_filter($datos, fn ($v) => $v !== null));
            }
        }

        if ($evento->tabla === 'persona' && ! empty($datos['apellido'])) {
            return ['persona' => true, 'texto' => "{$datos['apellido']}, " . ($datos['nombre'] ?? '')];
        }

        foreach (['id_persona_alumno', 'id_persona'] as $campo) {
            if (! empty($datos[$campo])) {
                return ['persona' => true, 'texto' => self::nombreDe('persona', 'id_persona', (int) $datos[$campo])];
            }
        }

        if (! empty($datos['id_profesor'])) {
            return ['persona' => true, 'texto' => self::nombreDe('profesor', 'id_profesor', (int) $datos['id_profesor'])];
        }

        $columna = self::COLUMNA_NOMBRE[$evento->tabla] ?? null;
        if ($columna && ! empty($datos[$columna])) {
            return ['persona' => false, 'texto' => (string) $datos[$columna]];
        }

        if (! empty($datos['id_materia'])) {
            return ['persona' => false, 'texto' => self::nombreDe('materia', 'id_materia', (int) $datos['id_materia'])];
        }

        return null;
    }

    private static function nombreDe(string $tabla, string $pk, int $id): ?string
    {
        return self::$cache["nombre.{$tabla}.{$id}"] ??= (function () use ($tabla, $pk, $id) {
            if ($tabla === 'persona') {
                $p = DB::table('persona')->where('id_persona', $id)->first(['nombre', 'apellido']);

                return $p ? "{$p->apellido}, {$p->nombre}" : null;
            }

            if ($tabla === 'profesor') {
                $idPersona = DB::table('profesor')->where('id_profesor', $id)->value('id_persona');

                return $idPersona ? self::nombreDe('persona', 'id_persona', (int) $idPersona) : null;
            }

            if ($tabla === 'materia') {
                $idNombre = DB::table('materia')->where('id_materia', $id)->value('id_nombre_materia');

                return $idNombre ? DB::table('nombre_materia')->where('id_nombre_materia', $idNombre)->value('nombre') : null;
            }

            if ($tabla === 'mesa_examen') {
                $idMateria = DB::table('mesa_examen')->where('id_mesa', $id)->value('id_materia');
                $materia = $idMateria ? self::nombreDe('materia', 'id_materia', (int) $idMateria) : null;

                return $materia ? "Mesa de {$materia}" : null;
            }

            $columna = self::COLUMNA_NOMBRE[$tabla] ?? null;

            return $columna ? ((string) DB::table($tabla)->where($pk, $id)->value($columna) ?: null) : null;
        })();
    }

    private static function fila(string $tabla, string $pk, int $id): ?object
    {
        return self::$cache["fila.{$tabla}.{$id}"] ??= DB::table($tabla)->where($pk, $id)->first();
    }

    private static function clavePrimaria(string $tabla): ?string
    {
        if (array_key_exists("pk.{$tabla}", self::$cache)) {
            return self::$cache["pk.{$tabla}"];
        }

        try {
            $clave = DB::selectOne("SHOW KEYS FROM `{$tabla}` WHERE Key_name = 'PRIMARY'");
            $pk = $clave->Column_name ?? null;
        } catch (Throwable) {
            $pk = null;
        }

        return self::$cache["pk.{$tabla}"] = $pk;
    }
}
