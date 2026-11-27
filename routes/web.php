<?php

use App\Http\Controllers\Admin\ActaController;
use App\Http\Controllers\Admin\AlertaController as AdminAlertaController;
use App\Http\Controllers\Admin\AlumnoController;
use App\Http\Controllers\Admin\HistorialPrevioController;
use App\Http\Controllers\Admin\CarreraController;
use App\Http\Controllers\Admin\DocumentacionController as AdminDocumentacionController;
use App\Http\Controllers\Admin\FichaInscripcionController as AdminFichaInscripcionController;
use App\Http\Controllers\Admin\HorarioCarreraController;
use App\Http\Controllers\Admin\InscripcionAdminController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\PanelController as AdminPanelController;
use App\Http\Controllers\Admin\PeriodoCursadaController;
use App\Http\Controllers\Admin\ProfesorController;
use App\Http\Controllers\Auth\CambioClaveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CorteActivoController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\CursadaController;
use App\Http\Controllers\Director\AlertaController;
use App\Http\Controllers\Director\AnioLectivoController as DirectorAnioLectivoController;
use App\Http\Controllers\Director\AuditoriaController;
use App\Http\Controllers\Director\LiquidacionHorasController;
use App\Http\Controllers\Director\CajaController;
use App\Http\Controllers\Director\PagoDocenteController;
use App\Http\Controllers\Director\ConfiguracionController as DirectorConfiguracionController;
use App\Http\Controllers\Director\ConfiguracionCuotaController;
use App\Http\Controllers\Director\ConfiguracionMatriculaController;
use App\Http\Controllers\Director\CuotaController as DirectorCuotaController;
use App\Http\Controllers\Director\EstadoPagosController;
use App\Http\Controllers\Director\PanelController as DirectorPanelController;
use App\Http\Controllers\Director\ReporteIngresosController;
use App\Http\Controllers\Director\UsuarioController as DirectorUsuarioController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\EstadoAcademicoController;
use App\Http\Controllers\FichaInscripcionController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MesaExamenController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

    Route::get('/recuperar-contrasena', [PasswordResetController::class, 'solicitarForm'])->name('password.solicitar');
    Route::get('/recuperar-contrasena/verificar', [PasswordResetController::class, 'verificarForm'])->name('password.verificar');
    Route::get('/recuperar-contrasena/nueva-clave', [PasswordResetController::class, 'nuevaForm'])->name('password.nueva');

    Route::middleware('throttle:password-reset')->group(function () {
        Route::post('/recuperar-contrasena', [PasswordResetController::class, 'solicitar']);
        Route::post('/recuperar-contrasena/verificar', [PasswordResetController::class, 'verificar']);
        Route::post('/recuperar-contrasena/nueva-clave', [PasswordResetController::class, 'nueva']);
    });
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/cambiar-clave', [CambioClaveController::class, 'edit'])->name('password.cambiar');
    Route::put('/cambiar-clave', [CambioClaveController::class, 'update']);
});

Route::post('/corte/seleccionar', [CorteActivoController::class, 'seleccionar'])->middleware('auth')->name('corte.seleccionar');

/*
|--------------------------------------------------------------------------
| Portal del Alumno
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'alumno', 'clave.cambiada'])->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');
    Route::post('/mi-carrera', [PanelController::class, 'elegirCarrera'])->name('mi-carrera');
    Route::get('/mi-estado-academico', [EstadoAcademicoController::class, 'index'])->name('estado-academico.index');

    Route::get('/cursada', [CursadaController::class, 'index'])->name('cursada.index'); /* */
    Route::post('/cursada/{materia}/inscribirme', [CursadaController::class, 'inscribir'])->name('cursada.inscribir');

    Route::get('/mesas-examen', [MesaExamenController::class, 'index'])->name('mesas-examen.index');
    Route::post('/mesas-examen/{mesa}/inscribirme', [MesaExamenController::class, 'inscribir'])->name('mesas-examen.inscribir');

    Route::get('/mis-inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
    Route::delete('/mis-inscripciones/{inscripcion}', [InscripcionController::class, 'cancelar'])->name('inscripciones.cancelar');
    Route::get('/mis-cuotas', [CuotaController::class, 'index'])->name('cuotas.index');

    Route::post('/mis-documentos/{documentoRequisito}', [DocumentacionController::class, 'store'])->name('documentacion.store');
    Route::get('/mis-documentos/{controlDocumentacion}/descarga', [DocumentacionController::class, 'descargar'])->name('documentacion.descarga');

    Route::get('/mis-datos', [PerfilController::class, 'edit'])->name('mis-datos.edit');
    Route::put('/mis-datos', [PerfilController::class, 'update'])->name('mis-datos.update');

    Route::get('/mi-ficha-inscripcion', [FichaInscripcionController::class, 'edit'])->name('ficha-inscripcion.edit');
    Route::put('/mi-ficha-inscripcion', [FichaInscripcionController::class, 'update'])->name('ficha-inscripcion.update');
});

/*
|--------------------------------------------------------------------------
| Panel de Secretaría / Administrador
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/panel', [AdminPanelController::class, 'index'])->name('panel.index');

    Route::get('/alertas', [AdminAlertaController::class, 'index'])->name('alertas.index');

    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
    Route::get('/alumnos/nuevo', [AlumnoController::class, 'create'])->name('alumnos.create');
    Route::post('/alumnos/nuevo', [AlumnoController::class, 'store'])->name('alumnos.store');
    Route::get('/alumnos/nuevo/academico', [AlumnoController::class, 'academicoForm'])->name('alumnos.academico');
    Route::post('/alumnos/nuevo/academico', [AlumnoController::class, 'academicoStore'])->name('alumnos.academico.store');
    Route::get('/alumnos/nuevo/acceso', [AlumnoController::class, 'acceso'])->name('alumnos.acceso');
    Route::post('/alumnos/nuevo/confirmar', [AlumnoController::class, 'confirmar'])->name('alumnos.confirmar');
    Route::get('/alumnos/historial/cargar', [HistorialPrevioController::class, 'index'])->name('alumnos.historial-previo.index');
    Route::post('/alumnos/historial/cargar', [HistorialPrevioController::class, 'store'])->name('alumnos.historial-previo.store');
    Route::post('/alumnos/historial/cargar-anio', [HistorialPrevioController::class, 'storeLote'])->name('alumnos.historial-previo.store-lote');
    Route::put('/alumnos/historial/cargar/{historial}', [HistorialPrevioController::class, 'update'])->name('alumnos.historial-previo.update');
    Route::delete('/alumnos/historial/cargar/{historial}', [HistorialPrevioController::class, 'destroy'])->name('alumnos.historial-previo.destroy');
    Route::get('/alumnos/{persona}', [AlumnoController::class, 'show'])->name('alumnos.show');
    Route::post('/alumnos/{persona}/carreras', [AlumnoController::class, 'inscribirCarrera'])->name('alumnos.carreras.store');
    Route::post('/alumnos/{persona}/cambiar-plan', [AlumnoController::class, 'cambiarPlan'])->name('alumnos.cambiar-plan');
    Route::put('/alumnos/{persona}/baja', [AlumnoController::class, 'baja'])->name('alumnos.baja');
    Route::put('/alumnos/{persona}/alta', [AlumnoController::class, 'alta'])->name('alumnos.alta');
    Route::put('/alumnos/{persona}/promover', [AlumnoController::class, 'promoverAnio'])->name('alumnos.promover');
    Route::put('/alumnos/{persona}/historial/{historial}/plazo', [AlumnoController::class, 'actualizarPlazoRegularidad'])->name('alumnos.historial.plazo');
    Route::get('/alumnos/{persona}/materias', [AlumnoController::class, 'materias'])->name('alumnos.materias');
    Route::post('/alumnos/{persona}/materias', [AlumnoController::class, 'materiasStore'])->name('alumnos.materias.store');
    Route::put('/alumnos/{persona}/historial/{historial}/condicion', [AlumnoController::class, 'actualizarCondicionHistorial'])->name('alumnos.historial.condicion');
    Route::put('/alumnos/{persona}/historial/{historial}/cerrar-cursada', [HistorialPrevioController::class, 'cerrarCursada'])->name('alumnos.historial.cerrar-cursada');

    Route::get('/documentacion', [AdminDocumentacionController::class, 'index'])->name('documentacion.index');
    Route::get('/documentacion/requisitos', [AdminDocumentacionController::class, 'requisitos'])->name('documentacion.requisitos');
    Route::post('/documentacion/requisitos', [AdminDocumentacionController::class, 'storeRequisito'])->name('documentacion.requisitos.store');
    Route::delete('/documentacion/requisitos/{documentoRequisito}', [AdminDocumentacionController::class, 'destroyRequisito'])->name('documentacion.requisitos.destroy');
    Route::get('/documentacion/{persona}', [AdminDocumentacionController::class, 'show'])->name('documentacion.show');
    Route::put('/documentacion/{controlDocumentacion}', [AdminDocumentacionController::class, 'actualizar'])->name('documentacion.actualizar');
    Route::get('/documentacion/archivo/{controlDocumentacion}', [AdminDocumentacionController::class, 'descargar'])->name('documentacion.descarga');

    Route::get('/ficha-inscripcion', [AdminFichaInscripcionController::class, 'index'])->name('ficha-inscripcion.index');
    Route::post('/ficha-inscripcion/secciones', [AdminFichaInscripcionController::class, 'storeSeccion'])->name('ficha-inscripcion.secciones.store');
    Route::put('/ficha-inscripcion/secciones/{seccion}', [AdminFichaInscripcionController::class, 'updateSeccion'])->name('ficha-inscripcion.secciones.update');
    Route::delete('/ficha-inscripcion/secciones/{seccion}', [AdminFichaInscripcionController::class, 'destroySeccion'])->name('ficha-inscripcion.secciones.destroy');
    Route::put('/ficha-inscripcion/secciones/{seccion}/mover', [AdminFichaInscripcionController::class, 'moverSeccion'])->name('ficha-inscripcion.secciones.mover');
    Route::post('/ficha-inscripcion/campos', [AdminFichaInscripcionController::class, 'storeCampo'])->name('ficha-inscripcion.campos.store');
    Route::put('/ficha-inscripcion/campos/{campo}', [AdminFichaInscripcionController::class, 'updateCampo'])->name('ficha-inscripcion.campos.update');
    Route::put('/ficha-inscripcion/campos/{campo}/activo', [AdminFichaInscripcionController::class, 'toggleCampo'])->name('ficha-inscripcion.campos.toggle');
    Route::put('/ficha-inscripcion/campos/{campo}/mover', [AdminFichaInscripcionController::class, 'moverCampo'])->name('ficha-inscripcion.campos.mover');
    Route::delete('/ficha-inscripcion/campos/{campo}', [AdminFichaInscripcionController::class, 'destroyCampo'])->name('ficha-inscripcion.campos.destroy');

    Route::get('/carreras', [CarreraController::class, 'index'])->name('carreras.index');
    Route::get('/carreras/nueva', [CarreraController::class, 'create'])->name('carreras.create');
    Route::post('/carreras', [CarreraController::class, 'store'])->name('carreras.store');
    Route::get('/carreras/{carrera}/editar', [CarreraController::class, 'edit'])->name('carreras.edit');
    Route::put('/carreras/{carrera}', [CarreraController::class, 'update'])->name('carreras.update');
    Route::get('/carreras/{carrera}/plan', [CarreraController::class, 'plan'])->name('carreras.plan');
    // Planes de estudio de la carrera (historial y vigencia).
    Route::get('/carreras/{carrera}/planes', [CarreraController::class, 'planes'])->name('carreras.planes');
    Route::post('/carreras/{carrera}/planes', [CarreraController::class, 'storePlan'])->name('carreras.planes.store');
    Route::put('/carreras/{carrera}/planes/{plan}', [CarreraController::class, 'updatePlan'])->name('carreras.planes.update');
    Route::delete('/carreras/{carrera}/planes/{plan}', [CarreraController::class, 'destroyPlan'])->name('carreras.planes.destroy');
    Route::post('/carreras/{carrera}/planes/{plan}/pasar-alumnos', [CarreraController::class, 'pasarAlumnos'])->name('carreras.planes.pasar-alumnos');
    Route::get('/carreras/{carrera}/materias', [CarreraController::class, 'materias'])->name('carreras.materias');
    Route::post('/carreras/{carrera}/materias', [CarreraController::class, 'storeMateria'])->name('carreras.materias.store');
    Route::put('/carreras/{carrera}/materias/{materia}', [CarreraController::class, 'updateMateria'])->name('carreras.materias.update');
    Route::put('/carreras/{carrera}/materias/{materia}/baja', [CarreraController::class, 'bajaMateria'])->name('carreras.materias.baja');
    Route::put('/carreras/{carrera}/materias/{materia}/reactivar', [CarreraController::class, 'reactivarMateria'])->name('carreras.materias.reactivar');
    Route::get('/carreras/{carrera}/correlativas', [CarreraController::class, 'correlativas'])->name('carreras.correlativas');
    Route::get('/carreras/{carrera}/correlativas/{principal}/editar', [CarreraController::class, 'editGrupoCorrelativas'])->name('carreras.correlativas.grupo.edit');
    Route::delete('/carreras/{carrera}/correlativas/{principal}', [CarreraController::class, 'destroyGrupoCorrelativas'])->name('carreras.correlativas.grupo.destroy');
    Route::post('/carreras/{carrera}/correlativas', [CarreraController::class, 'storeCorrelativa'])->name('carreras.correlativas.store');

    Route::get('/horarios', [HorarioCarreraController::class, 'index'])->name('horarios.index');
    Route::get('/horarios/pdf', [HorarioCarreraController::class, 'pdf'])->name('horarios.pdf');
    Route::post('/horarios/modulos', [HorarioCarreraController::class, 'storeModulo'])->name('horarios.modulos.store');
    Route::put('/horarios/modulos/{horarioModulo}', [HorarioCarreraController::class, 'updateModulo'])->name('horarios.modulos.update');
    Route::delete('/horarios/modulos/{horarioModulo}', [HorarioCarreraController::class, 'destroyModulo'])->name('horarios.modulos.destroy');
    Route::post('/horarios/clases', [HorarioCarreraController::class, 'storeClase'])->name('horarios.clases.store');
    Route::post('/horarios/franjas', [HorarioCarreraController::class, 'storeFranja'])->name('horarios.franjas.store');
    Route::put('/horarios/franjas/{horario}', [HorarioCarreraController::class, 'updateFranja'])->name('horarios.franjas.update');
    Route::delete('/horarios/clases/{horario}', [HorarioCarreraController::class, 'destroyClase'])->name('horarios.clases.destroy');

    Route::get('/cursada', [PeriodoCursadaController::class, 'index'])->name('cursada.index');
    Route::put('/cursada/{periodo}', [PeriodoCursadaController::class, 'toggle'])->name('cursada.toggle');
    Route::put('/cursada/{periodo}/preferencia-anio', [PeriodoCursadaController::class, 'guardarPreferenciaAnio'])->name('cursada.preferencia-anio');

    Route::get('/profesores', [ProfesorController::class, 'index'])->name('profesores.index');
    // Designaciones: profesor + materia + horas cátedra + tipo + alta/baja.
    // Inasistencias de docentes (Secretaría y Dirección); las injustificadas se descuentan del pago
    Route::get('/inasistencias-docentes', [\App\Http\Controllers\Admin\InasistenciaDocenteController::class, 'index'])->name('inasistencias.index');
    Route::post('/inasistencias-docentes', [\App\Http\Controllers\Admin\InasistenciaDocenteController::class, 'store'])->name('inasistencias.store');
    Route::put('/inasistencias-docentes/{licencia}/documentacion', [\App\Http\Controllers\Admin\InasistenciaDocenteController::class, 'documentacion'])->name('inasistencias.documentacion');
    Route::delete('/inasistencias-docentes/{licencia}', [\App\Http\Controllers\Admin\InasistenciaDocenteController::class, 'destroy'])->name('inasistencias.destroy');
    Route::post('/inasistencias-docentes/tipos', [\App\Http\Controllers\Admin\InasistenciaDocenteController::class, 'storeTipo'])->name('inasistencias.tipos.store');

    Route::get('/designaciones', [\App\Http\Controllers\Admin\DesignacionController::class, 'index'])->name('designaciones.index');
    Route::post('/designaciones', [\App\Http\Controllers\Admin\DesignacionController::class, 'store'])->name('designaciones.store');
    Route::put('/designaciones/{designacion}', [\App\Http\Controllers\Admin\DesignacionController::class, 'update'])->name('designaciones.update');
    Route::put('/designaciones/{designacion}/finalizar', [\App\Http\Controllers\Admin\DesignacionController::class, 'finalizar'])->name('designaciones.finalizar');
    Route::delete('/designaciones/{designacion}', [\App\Http\Controllers\Admin\DesignacionController::class, 'destroy'])->name('designaciones.destroy');
    Route::post('/profesores', [ProfesorController::class, 'store'])->name('profesores.store');
    Route::put('/profesores/{profesor}', [ProfesorController::class, 'update'])->name('profesores.update');
    Route::put('/profesores/{profesor}/baja', [ProfesorController::class, 'baja'])->name('profesores.baja');
    Route::put('/profesores/{profesor}/reactivar', [ProfesorController::class, 'reactivar'])->name('profesores.reactivar');
    Route::post('/profesores/especialidades', [ProfesorController::class, 'storeEspecialidad'])->name('profesores.especialidades.store');
    Route::post('/profesores/titulos', [ProfesorController::class, 'storeTitulo'])->name('profesores.titulos.store');
    Route::post('/profesores/provincias', [ProfesorController::class, 'storeProvincia'])->name('profesores.provincias.store');
    Route::post('/profesores/localidades', [ProfesorController::class, 'storeLocalidad'])->name('profesores.localidades.store');
    Route::put('/profesores/especialidades/{especialidad}', [ProfesorController::class, 'updateEspecialidad'])->name('profesores.especialidades.update');
    Route::delete('/profesores/especialidades/{especialidad}', [ProfesorController::class, 'destroyEspecialidad'])->name('profesores.especialidades.destroy');
    Route::post('/profesores/asignaciones', [ProfesorController::class, 'storeAsignacion'])->name('profesores.asignaciones.store');
    Route::delete('/profesores/asignaciones/{asignacion}', [ProfesorController::class, 'destroyAsignacion'])->name('profesores.asignaciones.destroy');
    Route::delete('/profesores/asignaciones/{asignacion}/horarios/{horario}', [ProfesorController::class, 'destroyHorario'])->name('profesores.asignaciones.horarios.destroy');

    Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
    Route::get('/mesas/nueva', [MesaController::class, 'create'])->name('mesas.create');
    Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
    Route::get('/mesas/{mesa}/editar', [MesaController::class, 'edit'])->name('mesas.edit');
    Route::put('/mesas/{mesa}', [MesaController::class, 'update'])->name('mesas.update');
    Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');

    Route::post('/mesas/turnos', [MesaController::class, 'storeTurno'])->name('mesas.turnos.store');
    Route::put('/mesas/turnos/{turno}', [MesaController::class, 'updateTurno'])->name('mesas.turnos.update');
    Route::delete('/mesas/turnos/{turno}', [MesaController::class, 'destroyTurno'])->name('mesas.turnos.destroy');

    Route::post('/mesas/llamados', [MesaController::class, 'storeLlamado'])->name('mesas.llamados.store');
    Route::put('/mesas/llamados/{llamado}', [MesaController::class, 'updateLlamado'])->name('mesas.llamados.update');
    Route::delete('/mesas/llamados/{llamado}', [MesaController::class, 'destroyLlamado'])->name('mesas.llamados.destroy');

    Route::get('/actas', [ActaController::class, 'index'])->name('actas.index');
    Route::get('/mesas/{mesa}/acta', [ActaController::class, 'show'])->name('mesas.acta');
    Route::get('/mesas/{mesa}/acta/pdf', [ActaController::class, 'pdf'])->name('mesas.acta.pdf');
    Route::post('/actas/{mesa}/cerrar', [ActaController::class, 'cerrar'])->name('actas.cerrar');

    Route::get('/inscripciones', [InscripcionAdminController::class, 'index'])->name('inscripciones.index');
    Route::put('/inscripciones/{inscripcionMesa}', [InscripcionAdminController::class, 'actualizar'])->name('inscripciones.actualizar');
    Route::delete('/inscripciones/{inscripcionMesa}', [InscripcionAdminController::class, 'destroy'])->name('inscripciones.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel de Dirección
|--------------------------------------------------------------------------
*/
Route::prefix('director')->name('director.')->middleware(['auth', 'director'])->group(function () {
    Route::get('/panel', [DirectorPanelController::class, 'index'])->name('panel.index');

    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');

    Route::get('/cuotas', [DirectorCuotaController::class, 'index'])->name('cuotas.index');
    Route::post('/cuotas/cobrar', [DirectorCuotaController::class, 'cobrar'])->name('cuotas.cobrar');
    Route::post('/cuotas/generar', [DirectorCuotaController::class, 'generar'])->name('cuotas.generar');

    Route::post('/matricula/cobrar', [DirectorCuotaController::class, 'cobrarMatricula'])->name('matricula.cobrar');
    Route::post('/matricula/generar', [DirectorCuotaController::class, 'generarMatricula'])->name('matricula.generar');

    // Configuración económica de la matrícula (monto/mes de habilitación/día
    // límite): se genera una sola matrícula por alumno con inscripción
    // activa en el año (no una por mes, a diferencia de la cuota).
    Route::get('/configuracion-matricula', [ConfiguracionMatriculaController::class, 'index'])->name('configuracion-matricula.index');
    Route::post('/configuracion-matricula', [ConfiguracionMatriculaController::class, 'store'])->name('configuracion-matricula.store');

    // Configuración económica de cuotas (monto/vencimiento/interés por
    // mora): acceden Director y Secretario Administrativo, igual que al
    // resto del panel de dirección. El Secretario (sin más) no entra.
    Route::get('/configuracion-cuotas', [ConfiguracionCuotaController::class, 'index'])->name('configuracion-cuotas.index');
    Route::post('/configuracion-cuotas', [ConfiguracionCuotaController::class, 'store'])->name('configuracion-cuotas.store');
    // Cuotas de Recursantes: precio por cantidad de materias, por año y carrera.
    Route::get('/tarifas-recursantes', [\App\Http\Controllers\Director\TarifaRecursanteController::class, 'index'])->name('tarifas-recursantes.index');
    Route::post('/tarifas-recursantes', [\App\Http\Controllers\Director\TarifaRecursanteController::class, 'store'])->name('tarifas-recursantes.store');
    Route::put('/tarifas-recursantes/{tarifa}', [\App\Http\Controllers\Director\TarifaRecursanteController::class, 'update'])->name('tarifas-recursantes.update');
    Route::delete('/tarifas-recursantes/{tarifa}', [\App\Http\Controllers\Director\TarifaRecursanteController::class, 'destroy'])->name('tarifas-recursantes.destroy');
    Route::post('/configuracion-cuotas/aumentos', [ConfiguracionCuotaController::class, 'storeAumento'])->name('configuracion-cuotas.aumentos.store');
    Route::put('/configuracion-cuotas/aumentos/{aumento}', [ConfiguracionCuotaController::class, 'updateAumento'])->name('configuracion-cuotas.aumentos.update');
    Route::delete('/configuracion-cuotas/aumentos/{aumento}', [ConfiguracionCuotaController::class, 'destroyAumento'])->name('configuracion-cuotas.aumentos.destroy');

    Route::get('/pagos', [EstadoPagosController::class, 'index'])->name('pagos.index');

    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::get('/caja/pdf', [CajaController::class, 'pdf'])->name('caja.pdf');
    Route::get('/caja/excel', [CajaController::class, 'excel'])->name('caja.excel');
    Route::post('/caja/gastos', [CajaController::class, 'storeGasto'])->name('caja.gastos.store');

    Route::get('/reportes/ingresos', [ReporteIngresosController::class, 'index'])->name('reportes.ingresos.index');
    Route::get('/reportes/ingresos/pdf', [ReporteIngresosController::class, 'pdf'])->name('reportes.ingresos.pdf');
    Route::get('/reportes/ingresos/excel', [ReporteIngresosController::class, 'excel'])->name('reportes.ingresos.excel');

    Route::get('/configuracion', [DirectorConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [DirectorConfiguracionController::class, 'update'])->name('configuracion.update');
    Route::delete('/configuracion', [DirectorConfiguracionController::class, 'destroy'])->name('configuracion.destroy');

    Route::post('/configuracion/imagenes', [DirectorConfiguracionController::class, 'storeImagenLanding'])->name('configuracion.imagenes.store');
    Route::delete('/configuracion/imagenes/{imagen}', [DirectorConfiguracionController::class, 'destroyImagenLanding'])->name('configuracion.imagenes.destroy');

    Route::post('/configuracion/cortes', [DirectorAnioLectivoController::class, 'store'])->name('configuracion.cortes.store');
    Route::put('/configuracion/cortes/{anio}/estado', [DirectorAnioLectivoController::class, 'actualizarEstado'])->name('configuracion.cortes.estado');
    Route::delete('/configuracion/cortes/{anio}', [DirectorAnioLectivoController::class, 'destroy'])->name('configuracion.cortes.destroy');

    // Liquidación de horas cátedra (valor por tipo de designación y total por docente)
    Route::get('/liquidacion-horas', [LiquidacionHorasController::class, 'index'])->name('liquidacion-horas.index');
    Route::get('/liquidacion-horas/calcular', [LiquidacionHorasController::class, 'calcular'])->name('liquidacion-horas.calcular');
    Route::post('/liquidacion-horas/valores', [LiquidacionHorasController::class, 'storeValor'])->name('liquidacion-horas.valores.store');
    Route::delete('/liquidacion-horas/valores/{valor}', [LiquidacionHorasController::class, 'destroyValor'])->name('liquidacion-horas.valores.destroy');

    Route::get('/pagos-docentes', [PagoDocenteController::class, 'index'])->name('pagos-docentes.index');
    Route::post('/pagos-docentes', [PagoDocenteController::class, 'store'])->name('pagos-docentes.store');
    Route::put('/pagos-docentes/{pago}', [PagoDocenteController::class, 'update'])->name('pagos-docentes.update');
    Route::delete('/pagos-docentes/{pago}', [PagoDocenteController::class, 'destroy'])->name('pagos-docentes.destroy');
    Route::post('/pagos-docentes/metodos-pago', [PagoDocenteController::class, 'storeMetodoPago'])->name('pagos-docentes.metodos-pago.store');
    Route::post('/pagos-docentes/estados', [PagoDocenteController::class, 'storeEstadoPago'])->name('pagos-docentes.estados.store');

    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

    Route::get('/usuarios', [DirectorUsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/nuevo', [DirectorUsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios/nuevo', [DirectorUsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [DirectorUsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [DirectorUsuarioController::class, 'update'])->name('usuarios.update');
    Route::put('/usuarios/{usuario}/baja', [DirectorUsuarioController::class, 'baja'])->name('usuarios.baja');
    Route::put('/usuarios/{usuario}/reactivar', [DirectorUsuarioController::class, 'reactivar'])->name('usuarios.reactivar');
    Route::put('/usuarios/{usuario}/reenviar-clave', [DirectorUsuarioController::class, 'reenviarClave'])->name('usuarios.reenviar-clave');
    Route::post('/usuarios/roles', [DirectorUsuarioController::class, 'storeRol'])->name('usuarios.roles.store');
    Route::put('/usuarios/roles/{rol}', [DirectorUsuarioController::class, 'updateRol'])->name('usuarios.roles.update');
    Route::delete('/usuarios/roles/{rol}', [DirectorUsuarioController::class, 'destroyRol'])->name('usuarios.roles.destroy');
});
