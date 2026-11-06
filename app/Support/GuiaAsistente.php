<?php

namespace App\Support;

use App\Models\Usuario;

/**
 * Catálogo de guías paso a paso del asistente "Goyo" (partials/guia-asistente).
 * Todo es local: no consulta ningún servicio externo.
 *
 * Para agregar una guía, sumá una entrada al arreglo: `ruta` es la pantalla a la que
 * lleva el botón "Ir a la pantalla" y `claves` son las palabras con las que la gente
 * podría pedirla (sin importar tildes ni mayúsculas).
 */
class GuiaAsistente
{
    private const SECRETARIA = 'Secretaría';
    private const DIRECCION = 'Dirección';

    /** Guías que puede ver el usuario, con la URL de cada pantalla ya resuelta. */
    public static function paraUsuario(?Usuario $usuario): array
    {
        if (! $usuario?->esStaff()) {
            return [];
        }

        return collect(self::catalogo())
            ->filter(fn (array $guia) => $guia['panel'] === self::SECRETARIA || $usuario->accedeADireccion())
            ->map(function (array $guia) {
                $guia['url'] = route($guia['ruta']);
                unset($guia['ruta']);

                return $guia;
            })
            ->values()
            ->all();
    }

    public static function catalogo(): array
    {
        return [
            // ---------- Alumnos ----------
            [
                'id' => 'alumno-nuevo',
                'titulo' => 'Registrar un alumno nuevo',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.create',
                'destacada' => true,
                'claves' => ['alumno', 'alumna', 'estudiante', 'inscribir alumno', 'matricular', 'alta alumno', 'nuevo alumno'],
                'pasos' => [
                    'En Datos Personales, cargá DNI, apellido, nombre y email, y tocá Continuar.',
                    'En Datos Académicos, elegí la carrera, el año lectivo y el año al que ingresa, y tocá Continuar.',
                    'En Acceso al Sistema, revisá los datos y tocá Registrar Alumno y generar clave.',
                ],
                'nota' => 'Entregale la clave inicial al alumno. El resto de los datos los completa desde su portal, en "Mi ficha de inscripción".',
            ],
            [
                'id' => 'alumno-buscar',
                'titulo' => 'Buscar un alumno y ver su ficha',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.index',
                'claves' => ['buscar alumno', 'encontrar alumno', 'ficha alumno', 'ver alumno', 'datos alumno', 'listado alumnos', 'consultar alumno'],
                'pasos' => [
                    'Filtrá por Carrera o Estado, o escribí el nombre o DNI en Buscar.',
                    'En la fila del alumno, tocá Ver ficha.',
                    'Ahí vas a ver los datos personales, la situación académica y las cuotas.',
                ],
            ],
            [
                'id' => 'alumno-baja',
                'titulo' => 'Dar de baja o de alta a un alumno',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.index',
                'claves' => ['baja alumno', 'dar de baja alumno', 'reactivar alumno', 'alta alumno', 'desactivar alumno', 'eliminar alumno', 'borrar alumno'],
                'pasos' => [
                    'Buscá al alumno y tocá Ver ficha.',
                    'Tocá Dar de baja. Si ya estaba dado de baja, vas a ver Dar de alta.',
                ],
            ],
            [
                'id' => 'alumno-materias',
                'titulo' => 'Inscribir a un alumno a materias',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.index',
                'claves' => ['inscribir materias', 'anotar materias', 'materias alumno', 'cursar materia', 'asignar materia alumno'],
                'pasos' => [
                    'Buscá al alumno y tocá Ver ficha.',
                    'Tocá Inscribir a materias.',
                    'Tildá las materias y tocá Inscribir a las materias seleccionadas.',
                ],
            ],
            [
                'id' => 'alumno-historial',
                'titulo' => 'Cargar el historial previo de un alumno',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.historial-previo.index',
                'claves' => ['historial', 'historial academico', 'equivalencia', 'equivalencias', 'materias aprobadas', 'notas anteriores', 'analitico'],
                'pasos' => [
                    'Buscá al alumno por apellido, nombre o DNI. Tiene que estar inscripto a una carrera.',
                    'Elegí carrera, materia, año lectivo y condición, y completá la fecha y la nota.',
                    'Si es una equivalencia, cargá la institución de origen y el N° de resolución.',
                    'Tocá Guardar Materia.',
                ],
                'nota' => 'Para corregir una materia ya cargada, editá los campos y tocá Guardar en su fila.',
            ],
            [
                'id' => 'alumno-promover',
                'titulo' => 'Promover a un alumno de año',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alumnos.index',
                'claves' => ['promover', 'promocion', 'pasar de año', 'subir de año', 'siguiente año'],
                'pasos' => [
                    'Buscá al alumno y tocá Ver ficha.',
                    'Tocá Promover a, seguido del año siguiente, y confirmá.',
                ],
                'nota' => 'El botón muestra cuántas materias del año tiene aprobadas. Hace falta la mayoría para que se active.',
            ],
            [
                'id' => 'ficha-inscripcion',
                'titulo' => 'Configurar la ficha de inscripción',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.ficha-inscripcion.index',
                'claves' => ['ficha inscripcion', 'formulario inscripcion', 'campos ficha', 'seccion ficha', 'agregar campo', 'preguntas ficha'],
                'pasos' => [
                    'Tocá + Nueva sección para sumar un bloque al formulario.',
                    'Dentro de la sección, tocá + Campo, y completá la etiqueta y el tipo de campo.',
                    'Marcá Campo obligatorio si hace falta y tocá Guardar campo.',
                    'Usá las flechas para reordenar y Ocultar o Mostrar para cambiar lo que ve el alumno.',
                ],
            ],

            // ---------- Profesores ----------
            [
                'id' => 'profesor-horario',
                'titulo' => 'Cargar el horario de un docente',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.profesores.index',
                'destacada' => true,
                'claves' => ['horario docente', 'horario profesor', 'horarios docentes', 'asignar materia', 'asignar profesor', 'asignacion', 'materia profesor', 'materia docente', 'aula'],
                'pasos' => [
                    'En Asignar materia y horario a profesor, elegí el profesor, la materia y el año lectivo.',
                    'Cargá la hora de inicio, la hora de fin y, si querés, el aula.',
                    'Tildá los días de cursada.',
                    'Tocá Guardar asignación.',
                ],
                'nota' => 'Para corregir un horario, buscalo en Asignaciones ya cargadas y tocá Editar.',
            ],
            [
                'id' => 'profesor-nuevo',
                'titulo' => 'Registrar un profesor nuevo',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.profesores.index',
                'destacada' => true,
                'claves' => ['profesor', 'profesora', 'docente', 'nuevo profesor', 'nuevo docente', 'alta docente', 'alta profesor', 'maestro'],
                'pasos' => [
                    'Abrí Agregar nuevo profesor al padrón, debajo del formulario de asignación.',
                    'Completá DNI, apellido, nombre, especialidad y condición. El email es opcional.',
                    'Tocá Guardar docente.',
                ],
                'nota' => 'Si la especialidad no aparece, agregala con el botón que está al lado de la lista.',
            ],
            [
                'id' => 'profesor-baja',
                'titulo' => 'Dar de baja o reactivar a un profesor',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.profesores.index',
                'claves' => ['baja profesor', 'baja docente', 'reactivar profesor', 'reactivar docente', 'eliminar profesor', 'eliminar docente', 'editar profesor', 'editar docente'],
                'pasos' => [
                    'Buscá al profesor en la tabla de docentes.',
                    'Usá el botón rojo para darlo de baja, o el verde para reactivarlo.',
                ],
                'nota' => 'Un profesor dado de baja ya no se puede asignar a nuevas materias ni tribunales.',
            ],

            // ---------- Carreras ----------
            [
                'id' => 'carrera-nueva',
                'titulo' => 'Crear una carrera nueva',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.carreras.create',
                'claves' => ['carrera', 'nueva carrera', 'crear carrera', 'tecnicatura', 'plan de estudio', 'plan estudios', 'oferta academica'],
                'pasos' => [
                    'Cargá el nombre oficial, tal como figura en la resolución.',
                    'Completá familia profesional, N° de resolución, duración y estado.',
                    'Tocá Siguiente para pasar a cargar las materias.',
                ],
            ],
            [
                'id' => 'materia-nueva',
                'titulo' => 'Agregar materias a una carrera',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.carreras.index',
                'claves' => ['materia', 'materias', 'asignatura', 'agregar materia', 'nueva materia', 'plan materias', 'baja materia', 'editar materia'],
                'pasos' => [
                    'En la tarjeta de la carrera, entrá a Materias.',
                    'En Agregar materia, completá N° de orden, nombre, año, período y régimen.',
                    'Tocá + Agregar.',
                ],
                'nota' => 'Desde Materias cargadas podés editar, dar de baja o reactivar cada una.',
            ],
            [
                'id' => 'correlativas',
                'titulo' => 'Configurar correlativas',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.carreras.index',
                'claves' => ['correlativa', 'correlativas', 'correlatividad', 'correlatividades', 'requisito materia'],
                'pasos' => [
                    'Entrá a las Materias de la carrera y tocá Configurar correlativas.',
                    'En Nueva correlativa, elegí la materia destino.',
                    'Elegí las materias requeridas y tocá Guardar correlativas.',
                ],
            ],

            // ---------- Horarios de carrera ----------
            [
                'id' => 'horario-carrera',
                'titulo' => 'Armar el horario de una carrera',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.horarios.index',
                'destacada' => true,
                'claves' => ['horario carrera', 'horarios carrera', 'horario', 'horarios', 'cuadro horario', 'modulo', 'modulos', 'grilla', 'cronograma'],
                'pasos' => [
                    'Elegí la carrera y el turno.',
                    'Tocá + Agregar módulo, cargá número, hora de inicio y hora de fin, y tocá Guardar módulo.',
                    'Hacé clic en la celda del día que quieras y escribí la materia y el docente.',
                    'Tocá Guardar.',
                ],
                'nota' => 'Los módulos de una misma carrera y turno no se pueden superponer, pero sí pueden ser seguidos (uno termina 09:00 y el otro arranca 09:00).',
            ],
            [
                'id' => 'horario-pdf',
                'titulo' => 'Descargar el horario en PDF',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.horarios.index',
                'claves' => ['pdf horario', 'descargar horario', 'imprimir horario', 'exportar horario', 'bajar horario'],
                'pasos' => [
                    'Elegí la carrera y el turno.',
                    'Tocá Descargar PDF.',
                ],
                'nota' => 'El botón aparece cuando ese horario ya tiene al menos un módulo cargado.',
            ],

            // ---------- Cursada ----------
            [
                'id' => 'cursada',
                'titulo' => 'Abrir o cerrar la inscripción a cursada',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.cursada.index',
                'claves' => ['cursada', 'inscripcion cursada', 'habilitar inscripcion', 'abrir inscripcion', 'cerrar inscripcion', 'periodo'],
                'pasos' => [
                    'Elegí la carrera.',
                    'Buscá el período y el año de cursada.',
                    'Tocá Abrir inscripción, o Cerrar inscripción si ya estaba abierta, y confirmá.',
                ],
                'nota' => 'Necesita un año lectivo activo. Si no hay ninguno, hay que activarlo desde Configuración, en el panel de Dirección.',
            ],

            // ---------- Mesas y actas ----------
            [
                'id' => 'mesa-nueva',
                'titulo' => 'Crear una mesa de examen',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.mesas.create',
                'destacada' => true,
                'claves' => ['mesa', 'mesas', 'mesa de examen', 'examen', 'examenes', 'final', 'finales', 'tribunal', 'nueva mesa', 'programar examen'],
                'pasos' => [
                    'Seleccioná la materia.',
                    'Cargá la apertura y el cierre de inscripción, y la fecha del examen.',
                    'Elegí el turno y el llamado. El cupo máximo es opcional.',
                    'Elegí el tribunal examinador y tocá Crear mesa.',
                ],
                'nota' => 'El examen tiene que ser al menos una semana después de abrir la inscripción, y un docente no puede ocupar dos roles del tribunal.',
            ],
            [
                'id' => 'turnos-llamados',
                'titulo' => 'Agregar turnos de examen o llamados',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.mesas.create',
                'claves' => ['turno examen', 'turnos examen', 'llamado', 'llamados', 'gestionar turnos', 'nuevo llamado'],
                'pasos' => [
                    'Dentro del formulario de la mesa, usá el botón que está al lado de Turno examen o de Llamado.',
                    'En la ventana que se abre, escribí el nombre y tocá Agregar.',
                ],
            ],
            [
                'id' => 'inscripciones-mesa',
                'titulo' => 'Aprobar inscripciones a mesas',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.inscripciones.index',
                'claves' => ['inscripciones', 'inscripcion mesa', 'aprobar inscripcion', 'aceptar inscripcion', 'rechazar inscripcion', 'solicitudes'],
                'pasos' => [
                    'Buscá la inscripción enviada por el alumno.',
                    'Tocá Aceptar o Rechazar.',
                    'Si ya rindió, podés cargar la nota y el resultado, y tocar Guardar.',
                ],
            ],
            [
                'id' => 'acta',
                'titulo' => 'Generar un acta de examen',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.actas.index',
                'destacada' => true,
                'claves' => ['acta', 'actas', 'acta de examen', 'notas examen', 'cargar notas', 'libro folio', 'imprimir acta'],
                'pasos' => [
                    'Elegí la carrera y la mesa.',
                    'Completá libro y folio, y cargá las notas de cada alumno.',
                    'Tocá Guardar borrador si todavía no terminaste.',
                    'Cuando esté lista, tocá Generar e imprimir acta.',
                ],
                'nota' => 'Las actas ya generadas quedan en Historial de actas, con la opción Ver / descargar PDF.',
            ],

            // ---------- Documentación ----------
            [
                'id' => 'documentacion-revisar',
                'titulo' => 'Revisar la documentación de un alumno',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.documentacion.index',
                'claves' => ['documentacion', 'documentos', 'papeles', 'revisar documentos', 'aprobar documento', 'rechazar documento', 'dni escaneado', 'certificado'],
                'pasos' => [
                    'Buscá al alumno y tocá Revisar.',
                    'Descargá cada documento enviado para verlo.',
                    'Tocá Aprobar o Rechazar.',
                ],
            ],
            [
                'id' => 'documentacion-requisitos',
                'titulo' => 'Configurar los documentos que se piden',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.documentacion.requisitos',
                'claves' => ['requisito', 'requisitos', 'nuevo requisito', 'pedir documento', 'documento obligatorio'],
                'pasos' => [
                    'En Agregar nuevo requisito, cargá el nombre del documento y una descripción breve.',
                    'Elegí si es Obligatorio u Opcional, y a qué carrera aplica.',
                    'Tocá Guardar requisito.',
                ],
            ],

            // ---------- Dirección ----------
            [
                'id' => 'cuota-generar',
                'titulo' => 'Generar cuotas a un alumno',
                'panel' => self::DIRECCION,
                'ruta' => 'director.cuotas.index',
                'claves' => ['generar cuota', 'generar cuotas', 'crear cuota', 'nueva cuota', 'cuota', 'cuotas', 'vencimiento', 'mensualidad'],
                'pasos' => [
                    'Buscá al alumno por nombre, apellido o DNI, y seleccionalo.',
                    'Abrí Generar cuota nueva.',
                    'Elegí año lectivo, día de vencimiento (1 a 28) y monto.',
                    'Tildá los meses y tocá Generar cuota(s).',
                ],
            ],
            [
                'id' => 'cuota-cobrar',
                'titulo' => 'Cobrar una cuota',
                'panel' => self::DIRECCION,
                'ruta' => 'director.cuotas.index',
                'destacada' => true,
                'claves' => ['cobrar', 'cobrar cuota', 'registrar pago cuota', 'pago alumno', 'pagar cuota', 'abonar cuota', 'cobro'],
                'pasos' => [
                    'Buscá al alumno por nombre, apellido o DNI, y seleccionalo.',
                    'Tildá las cuotas a cobrar.',
                    'Elegí la fecha y el medio de pago.',
                    'Tocá Confirmar cobro y registrar en Caja.',
                ],
                'nota' => 'Las cuotas se cobran en orden: no se puede saltear ningún mes. El cobro se asienta solo en el Libro de Caja.',
            ],
            [
                'id' => 'pagos-estado',
                'titulo' => 'Ver quién debe cuotas',
                'panel' => self::DIRECCION,
                'ruta' => 'director.pagos.index',
                'claves' => ['estado pagos', 'deudores', 'deuda', 'debe', 'morosos', 'cuotas pendientes', 'cuotas vencidas', 'al dia'],
                'pasos' => [
                    'Usá los filtros y tocá Filtrar.',
                    'En Estado de pagos por alumno, mirá las cuotas pagadas, pendientes y vencidas.',
                ],
            ],
            [
                'id' => 'caja-gasto',
                'titulo' => 'Registrar un gasto en caja',
                'panel' => self::DIRECCION,
                'ruta' => 'director.caja.index',
                'claves' => ['gasto', 'gastos', 'egreso', 'caja', 'libro de caja', 'registrar gasto', 'compra'],
                'pasos' => [
                    'Cargá el concepto, el monto y la fecha.',
                    'Tocá Registrar gasto.',
                ],
                'nota' => 'Los cobros de cuotas no se cargan acá: se asientan solos.',
            ],
            [
                'id' => 'caja-exportar',
                'titulo' => 'Descargar el libro de caja',
                'panel' => self::DIRECCION,
                'ruta' => 'director.caja.index',
                'claves' => ['exportar caja', 'pdf caja', 'excel caja', 'descargar caja', 'imprimir caja', 'planilla gastos'],
                'pasos' => [
                    'Elegí el mes.',
                    'En la Planilla de control de gastos, tocá PDF o Excel.',
                ],
            ],
            [
                'id' => 'pago-docente',
                'titulo' => 'Registrar el pago a un docente',
                'panel' => self::DIRECCION,
                'ruta' => 'director.pagos-docentes.index',
                'claves' => ['pago docente', 'pagar docente', 'pagar profesor', 'pago profesor', 'sueldo', 'sueldos', 'liquidacion', 'honorarios'],
                'pasos' => [
                    'Tocá Registrar pago.',
                    'Elegí el docente y cargá período abonado, fecha de pago y monto pagado.',
                    'Elegí método de pago y estado.',
                    'Tocá Guardar pago.',
                ],
            ],
            [
                'id' => 'reporte-ingresos',
                'titulo' => 'Sacar el reporte de ingresos',
                'panel' => self::DIRECCION,
                'ruta' => 'director.reportes.ingresos.index',
                'claves' => ['reporte', 'reportes', 'ingresos', 'recaudacion', 'cobros del mes', 'informe ingresos'],
                'pasos' => [
                    'Elegí las fechas Desde y Hasta, y si querés, concepto y medio de pago.',
                    'Tocá Filtrar.',
                    'Tocá PDF o Excel para descargarlo.',
                ],
            ],
            [
                'id' => 'usuario-nuevo',
                'titulo' => 'Crear un usuario de secretaría o dirección',
                'panel' => self::DIRECCION,
                'ruta' => 'director.usuarios.create',
                'claves' => ['usuario', 'usuarios', 'nuevo usuario', 'crear usuario', 'secretario', 'secretaria', 'director', 'rol', 'acceso sistema'],
                'pasos' => [
                    'Cargá DNI, apellido, nombre y email.',
                    'Elegí el rol.',
                    'Tocá Registrar usuario y generar clave.',
                ],
                'nota' => 'La clave inicial se muestra una sola vez: hay que entregársela a la persona. Los alumnos se dan de alta desde Alumnos.',
            ],
            [
                'id' => 'anio-lectivo',
                'titulo' => 'Generar o cerrar un año lectivo',
                'panel' => self::DIRECCION,
                'ruta' => 'director.configuracion.index',
                'claves' => ['año lectivo', 'anio lectivo', 'ciclo lectivo', 'nuevo año', 'cerrar año', 'reactivar año', 'corte'],
                'pasos' => [
                    'Andá a la sección Años lectivos.',
                    'Para crear uno, escribí el año y tocá Generar año lectivo.',
                    'Para cambiar uno existente, tocá Cerrar o Reactivar.',
                ],
            ],
            [
                'id' => 'configuracion',
                'titulo' => 'Cambiar los datos de la institución',
                'panel' => self::DIRECCION,
                'ruta' => 'director.configuracion.index',
                'claves' => ['configuracion', 'datos institucion', 'logo', 'direccion instituto', 'telefono', 'portada', 'landing', 'imagenes portada'],
                'pasos' => [
                    'En Datos de la institución, editá lo que necesites.',
                    'Tocá Guardar cambios.',
                    'Para la página pública, subí fotos en Imágenes de portada y tocá Subir imágenes.',
                ],
            ],
            [
                'id' => 'auditoria',
                'titulo' => 'Ver quién hizo un cambio',
                'panel' => self::DIRECCION,
                'ruta' => 'director.auditoria.index',
                'claves' => ['auditoria', 'quien cambio', 'quien modifico', 'historial cambios', 'registro acciones', 'quien borro'],
                'pasos' => [
                    'Filtrá por tabla, acción o fechas.',
                    'Tocá Filtrar y revisá el Registro de acciones.',
                ],
            ],
        ];
    }
}
