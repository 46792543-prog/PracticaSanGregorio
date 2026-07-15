<?php

use App\Http\Controllers\Admin\ActaController;
use App\Http\Controllers\Admin\AlumnoController;
use App\Http\Controllers\Admin\CarreraController;
use App\Http\Controllers\Admin\DocumentacionController;
use App\Http\Controllers\Admin\InscripcionAdminController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\PanelController as AdminPanelController;
use App\Http\Controllers\Admin\ProfesorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\Director\CajaController;
use App\Http\Controllers\Director\CuotaController as DirectorCuotaController;
use App\Http\Controllers\Director\PanelController as DirectorPanelController;
use App\Http\Controllers\EstadoAcademicoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\MesaExamenController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/recuperar-contrasena', [PasswordResetController::class, 'solicitarForm'])->name('password.solicitar');
    Route::post('/recuperar-contrasena', [PasswordResetController::class, 'solicitar']);
    Route::get('/recuperar-contrasena/verificar', [PasswordResetController::class, 'verificarForm'])->name('password.verificar');
    Route::post('/recuperar-contrasena/verificar', [PasswordResetController::class, 'verificar']);
    Route::get('/recuperar-contrasena/nueva-clave', [PasswordResetController::class, 'nuevaForm'])->name('password.nueva');
    Route::post('/recuperar-contrasena/nueva-clave', [PasswordResetController::class, 'nueva']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Portal del Alumno
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');
    Route::get('/mi-estado-academico', [EstadoAcademicoController::class, 'index'])->name('estado-academico.index');

    Route::get('/mesas-examen', [MesaExamenController::class, 'index'])->name('mesas-examen.index');
    Route::post('/mesas-examen/{mesa}/inscribirme', [MesaExamenController::class, 'inscribir'])->name('mesas-examen.inscribir');

    Route::get('/mis-inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
    Route::get('/mis-cuotas', [CuotaController::class, 'index'])->name('cuotas.index');

    Route::get('/mis-datos', [PerfilController::class, 'edit'])->name('mis-datos.edit');
    Route::put('/mis-datos', [PerfilController::class, 'update'])->name('mis-datos.update');
});

/*
|--------------------------------------------------------------------------
| Panel de Secretaría / Administrador
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/panel', [AdminPanelController::class, 'index'])->name('panel.index');

    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
    Route::get('/alumnos/nuevo', [AlumnoController::class, 'create'])->name('alumnos.create');
    Route::post('/alumnos/nuevo', [AlumnoController::class, 'store'])->name('alumnos.store');
    Route::get('/alumnos/nuevo/academico', [AlumnoController::class, 'academicoForm'])->name('alumnos.academico');
    Route::post('/alumnos/nuevo/academico', [AlumnoController::class, 'academicoStore'])->name('alumnos.academico.store');
    Route::get('/alumnos/nuevo/acceso', [AlumnoController::class, 'acceso'])->name('alumnos.acceso');
    Route::post('/alumnos/nuevo/confirmar', [AlumnoController::class, 'confirmar'])->name('alumnos.confirmar');
    Route::get('/alumnos/{user}', [AlumnoController::class, 'show'])->name('alumnos.show');
    Route::put('/alumnos/{user}/baja', [AlumnoController::class, 'baja'])->name('alumnos.baja');

    Route::get('/documentacion', [DocumentacionController::class, 'index'])->name('documentacion.index');
    Route::get('/documentacion/requisitos', [DocumentacionController::class, 'requisitos'])->name('documentacion.requisitos');
    Route::post('/documentacion/requisitos', [DocumentacionController::class, 'storeRequisito'])->name('documentacion.requisitos.store');
    Route::delete('/documentacion/requisitos/{documentoRequisito}', [DocumentacionController::class, 'destroyRequisito'])->name('documentacion.requisitos.destroy');
    Route::get('/documentacion/{user}', [DocumentacionController::class, 'show'])->name('documentacion.show');
    Route::put('/documentacion/{documentoAlumno}', [DocumentacionController::class, 'actualizar'])->name('documentacion.actualizar');

    Route::get('/carreras', [CarreraController::class, 'index'])->name('carreras.index');
    Route::get('/carreras/nueva', [CarreraController::class, 'create'])->name('carreras.create');
    Route::post('/carreras', [CarreraController::class, 'store'])->name('carreras.store');
    Route::get('/carreras/{carrera}/plan', [CarreraController::class, 'plan'])->name('carreras.plan');
    Route::get('/carreras/{carrera}/materias', [CarreraController::class, 'materias'])->name('carreras.materias');
    Route::post('/carreras/{carrera}/materias', [CarreraController::class, 'storeMateria'])->name('carreras.materias.store');
    Route::get('/carreras/{carrera}/correlativas', [CarreraController::class, 'correlativas'])->name('carreras.correlativas');
    Route::post('/carreras/{carrera}/correlativas', [CarreraController::class, 'storeCorrelativa'])->name('carreras.correlativas.store');
    Route::delete('/correlativas/{correlativa}', [CarreraController::class, 'destroyCorrelativa'])->name('carreras.correlativas.destroy');

    Route::get('/profesores', [ProfesorController::class, 'index'])->name('profesores.index');
    Route::post('/profesores', [ProfesorController::class, 'store'])->name('profesores.store');
    Route::post('/profesores/asignaciones', [ProfesorController::class, 'storeAsignacion'])->name('profesores.asignaciones.store');
    Route::delete('/profesores/asignaciones/{asignacion}', [ProfesorController::class, 'destroyAsignacion'])->name('profesores.asignaciones.destroy');

    Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
    Route::get('/mesas/nueva', [MesaController::class, 'create'])->name('mesas.create');
    Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
    Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');

    Route::get('/actas', [ActaController::class, 'index'])->name('actas.index');
    Route::post('/actas/{mesa}/guardar', [ActaController::class, 'guardar'])->name('actas.guardar');
    Route::post('/actas/{mesa}/generar', [ActaController::class, 'generar'])->name('actas.generar');

    Route::get('/inscripciones', [InscripcionAdminController::class, 'index'])->name('inscripciones.index');
    Route::put('/inscripciones/{inscripcionMesa}', [InscripcionAdminController::class, 'actualizar'])->name('inscripciones.actualizar');
});

/*
|--------------------------------------------------------------------------
| Panel de Dirección
|--------------------------------------------------------------------------
*/
Route::prefix('director')->name('director.')->middleware(['auth', 'director'])->group(function () {
    Route::get('/panel', [DirectorPanelController::class, 'index'])->name('panel.index');

    Route::get('/cuotas', [DirectorCuotaController::class, 'index'])->name('cuotas.index');
    Route::post('/cuotas/cobrar', [DirectorCuotaController::class, 'cobrar'])->name('cuotas.cobrar');

    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/gastos', [CajaController::class, 'storeGasto'])->name('caja.gastos.store');
});
