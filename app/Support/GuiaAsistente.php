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
                'id' => 'profesor-nuevo',
                'titulo' => 'Registrar un profesor nuevo',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.profesores.index',
                'destacada' => true,
                'claves' => ['profesor', 'profesora', 'docente', 'nuevo profesor', 'nuevo docente', 'alta docente', 'alta profesor', 'maestro'],
                'pasos' => [
                    'Arriba de todo, en Agregar nuevo profesor, completá DNI, apellido, nombre, condición y especialidades.',
                    'Si querés, cargá sexo, CUIL (el botón Calcular lo completa con el DNI y el sexo), fecha de ingreso, email y sus títulos.',
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

            // ---------- Horarios ----------
            [
                'id' => 'horario-carrera',
                'titulo' => 'Armar el horario (grilla de clases)',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.horarios.index',
                'destacada' => true,
                'claves' => ['horario carrera', 'horarios carrera', 'horario', 'horarios', 'cuadro horario', 'modulo', 'modulos', 'grilla', 'cronograma'],
                'pasos' => [
                    'En Horarios, en Franja horaria, elegí el docente y la materia (podés filtrar por carrera).',
                    'Elegí el día, el horario desde y hasta (o tocá un botón rápido: +40, +80…) y el turno.',
                    'Mirá el recuadro Superposición: si está en verde, tocá Guardar franja.',
                    'Abajo, en Horario de cursada, mirá la semana de un docente o de un curso (en grilla o en tabla).',
                ],
                'nota' => 'No deja guardar si el docente ya da otra clase a esa hora, si ese curso ya tiene otra clase a esa hora (tampoco dos docentes de la misma práctica), si la franja queda fuera del horario de su turno (Mañana 07–13, Tarde 13–19, Noche 19–24), si se pasa de las horas cátedra o si la duración no es múltiplo de 40 minutos.',
            ],
            [
                'id' => 'horario-pdf',
                'titulo' => 'Descargar el horario en PDF',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.horarios.index',
                'claves' => ['pdf horario', 'descargar horario', 'imprimir horario', 'exportar horario', 'bajar horario'],
                'pasos' => [
                    'En Horario de cursada, buscá un docente o elegí un curso.',
                    'Tocá el botón PDF.',
                ],
                'nota' => 'El botón aparece cuando ese docente o curso tiene al menos una franja cargada.',
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
                    'Normalmente no hace falta: las cuotas se generan solas al guardar la Configuración de Cuotas, al inscribir al alumno y al abrirlo en Registrar Cuotas.',
                    'Solo para un caso excepcional: buscá al alumno, abrí "Generar cuota extra (caso excepcional)".',
                    'Elegí año lectivo, día de vencimiento y monto, tildá los meses y tocá Generar cuota(s).',
                ],
                'nota' => 'Antes hay que tener la matrícula del año cobrada.',
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
                'titulo' => 'Registrar un egreso en caja',
                'panel' => self::DIRECCION,
                'ruta' => 'director.caja.index',
                'claves' => ['gasto', 'gastos', 'egreso', 'egresos', 'caja', 'libro de caja', 'registrar gasto', 'registrar egreso', 'compra', 'proveedor'],
                'pasos' => [
                    'Elegí la categoría (librería, limpieza, servicios, mantenimiento, mobiliario u otros).',
                    'Cargá el concepto, el beneficiario o proveedor (opcional), el monto y la fecha.',
                    'Tocá Registrar egreso y confirmá.',
                ],
                'nota' => 'Cuotas, matrículas y honorarios docentes no se cargan acá: se asientan solos.',
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
                'claves' => ['pago docente', 'pagar docente', 'pagar profesor', 'pago profesor', 'sueldo', 'sueldos', 'honorarios'],
                'pasos' => [
                    'Tocá Registrar pago.',
                    'Elegí el docente, el período abonado y la fecha de pago: el monto se calcula solo con sus horas cátedra y no se puede cambiar.',
                    'Elegí método de pago y estado.',
                    'Tocá Guardar pago.',
                ],
            ],
            [
                'id' => 'liquidacion-horas',
                'titulo' => 'Valor de la hora cátedra y liquidación de docentes',
                'panel' => self::DIRECCION,
                'ruta' => 'director.liquidacion-horas.index',
                'claves' => ['liquidacion', 'hora catedra', 'horas catedra', 'valor hora', 'cuanto cobra', 'control de horas'],
                'pasos' => [
                    'Entrá a Liquidación de horas.',
                    'En la tabla, tocá la celda de la carrera y el tipo (Titular, Interino, Suplente, Provisional), cargá el valor y desde qué mes rige.',
                    'Elegí el mes: cada docente cobra horas semanales × 4 × valor de la hora de su carrera y tipo, menos las inasistencias que descuentan.',
                    'Tocá un docente para ver el detalle por materia, o Registrar pago para pagarle.',
                ],
            ],
            [
                'id' => 'correo-deuda-alumno',
                'titulo' => 'Enviar un correo a un alumno con cuotas vencidas',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alertas.index',
                'claves' => ['correo', 'mail', 'email', 'avisar', 'aviso', 'deuda', 'cuotas vencidas', 'comunicacion', 'enviar correo'],
                'pasos' => [
                    'Entrá a Central de Alertas.',
                    'En la alerta del alumno tocá Enviar correo (o marcá varios y tocá Preparar correos).',
                    'Revisá o editá el asunto y el mensaje; con Vista previa ves cómo le llega.',
                    'Tocá Enviar y confirmá. Cada alumno recibe su propio correo.',
                    'El resultado queda en el Historial de comunicaciones, abajo.',
                ],
                'nota' => 'Nunca se envían correos solos: siempre los envía Secretaría a mano.',
            ],
            [
                'id' => 'inasistencias-docentes',
                'titulo' => 'Cargar una inasistencia de un docente',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.inasistencias.index',
                'claves' => ['inasistencia', 'inasistencias', 'licencia', 'licencias', 'falta docente', 'falto', 'ausencia', 'descuento', 'descontar', 'certificado'],
                'pasos' => [
                    'Entrá a Inasistencias docentes y tocá Registrar inasistencia.',
                    'Buscá el docente y elegí Desde / Hasta (vacío = mismo día; puede ser una fecha futura).',
                    'Elegí el tipo de licencia y si presentó la documentación (Presentada o Pendiente; las sin goce no llevan).',
                    'Revisá las clases que pierde (salen del Horario, con sus horas cátedra) y tocá Guardar.',
                    'Se descuentan las sin goce de sueldo y las con documentación pendiente; con el lápiz se actualiza la documentación después.',
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
            // ---------- Designaciones ----------
            [
                'id' => 'designacion-nueva',
                'titulo' => 'Designar a un profesor en una materia',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.designaciones.index',
                'destacada' => true,
                'claves' => ['designacion', 'designar', 'designaciones', 'asignar materia', 'asignar profesor', 'docente materia', 'titular', 'interino', 'suplente', 'resolucion'],
                'pasos' => [
                    'Elegí la carrera, el turno y la materia.',
                    'Buscá al docente y elegí el tipo: Titular, Interino, Suplente o Provisional.',
                    'En una práctica, elegí cuántas horas cátedra da este docente; en una materia común son todas las de la materia.',
                    'Cargá la fecha de alta (y la de baja si tiene) y el número de resolución, y tocá Guardar designación.',
                ],
                'nota' => 'Una materia común tiene un solo docente por turno. En una práctica se reparten las horas entre varios docentes sin pasarse del total.',
            ],

            // ---------- Cuotas y matrícula (Dirección) ----------
            [
                'id' => 'configuracion-cuotas',
                'titulo' => 'Configurar el valor de la cuota y los aumentos',
                'panel' => self::DIRECCION,
                'ruta' => 'director.configuracion-cuotas.index',
                'claves' => ['configurar cuota', 'configuracion cuotas', 'valor cuota', 'precio cuota', 'monto cuota', 'aumento', 'aumentos', 'subir cuota', 'mora', 'interes', 'vencimiento cuota'],
                'pasos' => [
                    'Elegí el período lectivo y la carrera, el día de vencimiento y de qué mes a qué mes se cobra.',
                    'Cargá el monto (se separa solo de a miles) y el interés por mora, y tocá Guardar y generar cuotas.',
                    'Para un aumento, en Aumentos elegí la carrera, desde qué mes rige y el monto nuevo.',
                ],
                'nota' => 'El aumento rige desde su mes en adelante: los meses anteriores se cobran al precio viejo aunque se paguen después. Las cuotas pagadas no se tocan.',
            ],
            [
                'id' => 'cuotas-recursantes',
                'titulo' => 'Configurar las cuotas de recursantes',
                'panel' => self::DIRECCION,
                'ruta' => 'director.tarifas-recursantes.index',
                'claves' => ['recursante', 'recursantes', 'recursa', 'recursar', 'cuota recursante', 'precio recursante'],
                'pasos' => [
                    'Elegí el año lectivo, la carrera y la cantidad de materias que recursa.',
                    'Cargá el monto de la cuota para esa cantidad y tocá Guardar precio.',
                ],
                'nota' => 'El sistema cuenta cuántas materias cursa cada recursante y le cobra el precio de esa cantidad.',
            ],
            [
                'id' => 'configuracion-matricula',
                'titulo' => 'Configurar y cobrar la matrícula',
                'panel' => self::DIRECCION,
                'ruta' => 'director.configuracion-matricula.index',
                'claves' => ['matricula', 'matriculas', 'valor matricula', 'cobrar matricula', 'configurar matricula', 'inscripcion anual'],
                'pasos' => [
                    'En Configuración de Matrícula, cargá el monto por año lectivo y carrera.',
                    'Para cobrarla, en Registrar Cuotas buscá al alumno y tocá Cobrar matrícula.',
                ],
                'nota' => 'Sin la matrícula del año cobrada no se pueden cobrar las cuotas.',
            ],
            [
                'id' => 'caja-categorias',
                'titulo' => 'Agregar o editar una categoría de egreso',
                'panel' => self::DIRECCION,
                'ruta' => 'director.caja.index',
                'claves' => ['categoria', 'categorias', 'categoria egreso', 'tipo de gasto', 'nueva categoria', 'editar categoria'],
                'pasos' => [
                    'En Libro de caja, en Registrar egreso, tocá el + al lado de Categoría para agregar una.',
                    'Para cambiarle el nombre, elegila y tocá el lápiz.',
                ],
                'nota' => '"Pago de honorarios docentes" no se edita ni se carga a mano: se asienta solo desde Pago de Docentes.',
            ],
            [
                'id' => 'planes-estudio',
                'titulo' => 'Crear un plan de estudio o cambiar de plan a un alumno',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.carreras.index',
                'claves' => ['plan', 'planes', 'plan de estudio', 'vigencia plan', 'cambiar plan', 'plan nuevo', 'resolucion plan'],
                'pasos' => [
                    'En Carreras y planes, en la carrera tocá Planes y creá el plan con su vigencia.',
                    'Para pasar a un alumno a otro plan, entrá a su ficha y usá Cambiar plan.',
                ],
            ],

            // ---------- Preguntas frecuentes (Goyo responde con una explicación) ----------
            [
                'id' => 'faq-pago-docente',
                'titulo' => '¿Cómo se calcula lo que cobra un docente?',
                'panel' => self::DIRECCION,
                'ruta' => 'director.liquidacion-horas.index',
                'claves' => ['como se calcula pago docente', 'cuanto cobra profesor', 'calculo sueldo', 'por que cobra', 'monto docente', 'de donde sale el monto'],
                'respuesta' => 'Por cada materia designada: horas cátedra semanales × 4 = horas del mes; esas horas × el valor de la hora cátedra de su carrera y tipo (Titular, Interino…). A eso se le restan las horas de inasistencias que descuentan. En Pago de Docentes el monto sale solo y queda bloqueado; con el ojito ves el detalle.',
            ],
            [
                'id' => 'faq-inasistencia-descuenta',
                'titulo' => '¿Cuándo una inasistencia descuenta del sueldo?',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.inasistencias.index',
                'claves' => ['inasistencia descuenta', 'falta descuenta', 'descuento falta', 'licencia descuenta', 'justificada', 'certificado', 'con goce', 'sin goce'],
                'respuesta' => 'Descuentan las licencias sin goce de sueldo (ej. inasistencia injustificada) y las con goce cuya documentación esté Pendiente. Cuando el docente presenta el certificado, marcala como Presentada con el lápiz y deja de descontar. Si el mes ya se pagó, no se puede modificar.',
            ],
            [
                'id' => 'faq-sin-cuotas',
                'titulo' => '¿Por qué a un alumno no le aparecen cuotas?',
                'panel' => self::DIRECCION,
                'ruta' => 'director.cuotas.index',
                'claves' => ['no aparecen cuotas', 'no tiene cuotas', 'no se generan cuotas', 'alumno sin cuotas', 'faltan cuotas'],
                'respuesta' => 'Las cuotas se generan solas si la carrera tiene Configuración de Cuotas para ese año y el alumno tiene inscripción activa. Abrilo en Registrar Cuotas y se completan las que falten. Si sigue sin cuotas, revisá que exista la configuración de su carrera y año, y que no sea de condición Cursada completa (esos pagan solo el mes en que rinden).',
            ],
            [
                'id' => 'faq-aumento',
                'titulo' => '¿Cómo afecta un aumento a las cuotas que se deben?',
                'panel' => self::DIRECCION,
                'ruta' => 'director.configuracion-cuotas.index',
                'claves' => ['aumento cuotas adeudadas', 'precio viejo', 'aumento desde', 'cuota vieja', 'deuda aumento'],
                'respuesta' => 'Cada mes se cobra a su propio precio: si la cuota subió en agosto, de marzo a julio se cobran al precio anterior aunque se paguen después, y de agosto en adelante al nuevo. La mora se calcula sobre el monto de cada mes. Las cuotas pagadas nunca cambian.',
            ],
            [
                'id' => 'faq-turnos-horario',
                'titulo' => '¿En qué horario va cada turno?',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.horarios.index',
                'claves' => ['turno manana', 'turno tarde', 'turno noche', 'horario turno', 'que horario', 'fuera de turno'],
                'respuesta' => 'Mañana: 07:00 a 13:00. Tarde: 13:00 a 19:00. Noche: 19:00 a 24:00. Una franja tiene que quedar entera dentro del horario de su turno, si no el sistema no la deja guardar.',
            ],
            [
                'id' => 'faq-pago-docente-caja',
                'titulo' => '¿El pago a un docente se anota solo en la caja?',
                'panel' => self::DIRECCION,
                'ruta' => 'director.pagos-docentes.index',
                'claves' => ['pago docente caja', 'honorarios caja', 'egreso docente', 'se anota en caja', 'libro de caja docente'],
                'respuesta' => 'Sí: cuando el pago queda en estado Pagado se genera solo un egreso "Pago de honorarios docentes" con el docente, el período, la fecha y el importe. Si lo editás se actualiza, si vuelve a Pendiente o lo eliminás se quita. Nunca se duplica.',
            ],
            [
                'id' => 'faq-correos',
                'titulo' => '¿El sistema manda correos solo?',
                'panel' => self::SECRETARIA,
                'ruta' => 'admin.alertas.index',
                'claves' => ['correo automatico', 'mail automatico', 'recordatorio', 'avisos automaticos', 'manda solo'],
                'respuesta' => 'No. Los correos a alumnos los envía Secretaría a mano desde la Central de Alertas: revisás el mensaje, mirás la vista previa, confirmás y recién ahí sale. Cada alumno recibe su propio correo y todo queda en el Historial de comunicaciones.',
            ],
        ];
    }
}
