<?php

use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\AdminMedicoController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Middleware\SoloAdministrador;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*para la landing page*/

Route::get('/', function () {
    return view('publica.landing');
});
Route::get('/equipo', [MedicoController::class, 'index'])->name('show.equipo');


/*PACIENTE*/

Route::get('/registrarse-pacinte', [PacienteController::class, 'create'])->name('create.paciente');

Route::post('/guardar-paciente', [PacienteController::class, 'store'])->name('store.paciente');

Route::get('/iniciar-sesion-paciente', [PacienteController::class, 'mostrarLogin'])->name('login.paciente');

Route::post('/ingresar-paciente', [PacienteController::class, 'autenticar'])->name('paciente.autenticar');

Route::middleware(['auth'])->group(function () {
    Route::put('/mi-cuenta-paciente', [PacienteController::class, 'update'])->name('paciente.update');

    Route::get('/mi-cuenta-paciente', [PacienteController::class, 'edit'])->name('paciente.edit');

    Route::get('/panel-paciente', [PacienteController::class, 'panel'])->name('usuarios.paciente.panel');
});


/*MEDICO*/
Route::get('/iniciar-sesion-profesional', [MedicoController::class, 'mostrarLogin'])->name('login.medico');

Route::post('/ingresar-profesional', [MedicoController::class, 'autenticar'])->name('medico.autenticar');

Route::get('/panel-profesional', [MedicoController::class, 'panel'])->name('usuarios.medico.panel');


// La duracion se configura en cada especialidad, no en el perfil del medico.
// Conservamos la ruta anterior como redireccion para enlaces guardados.
Route::get('/profesional/configurar-turno', fn () => redirect()->route('medico.edit'))
    ->middleware('auth')->name('medico.configurar-turno');

Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta-profesional', [MedicoController::class, 'edit'])->name('medico.edit');
    Route::put('/mi-cuenta-profesional', [MedicoController::class, 'update'])->name('medico.update');
});
/* Rutas de disponibilidad del medico: sus franjas no dependen de una duracion propia. */
Route::middleware(['auth'])->prefix('profesional')->name('medico.')->group(function () {
    Route::get('/disponibilidades', [DisponibilidadController::class, 'index'])
        ->name('disponibilidades.index');

    Route::get('/disponibilidades/crear', [DisponibilidadController::class, 'create'])
        ->name('disponibilidades.create');

    Route::post('/disponibilidades', [DisponibilidadController::class, 'store'])
        ->name('disponibilidades.store');

    Route::get('/disponibilidades/{id}', [DisponibilidadController::class, 'show'])
        ->name('disponibilidades.show');

    Route::get('/disponibilidades/{id}/editar', [DisponibilidadController::class, 'edit'])
        ->name('disponibilidades.edit');

    Route::put('/disponibilidades/{id}', [DisponibilidadController::class, 'update'])
        ->name('disponibilidades.update');

    Route::delete('/disponibilidades/{id}', [DisponibilidadController::class, 'delete'])
        ->name('disponibilidades.delete');
});



/*ADMIN*/
Route::get('/iniciar-sesion-admin', function () {
    return view('usuarios.admin.login');
})->name('login.admin');

Route::middleware(['auth'])->group(function () {
    Route::get('/mi-cuenta-admin', [UserController::class, 'edit'])->name('admin.edit');

    Route::put('/mi-cuenta-admin', [UserController::class, 'update'])->name('admin.update');
});

Route::post('/ingresar-admin', [UserController::class, 'autenticar'])->name('admin.autenticar');

// Panel administrativo: ruta propia para evitar duplicar /panel-paciente.
Route::get('/panel-admin', [AdminDashboardController::class, 'index'])
    ->middleware(SoloAdministrador::class)->name('usuarios.admin.panel');



/*para el cerrar sesion de todos los roles*/
Route::post('/logout', [UserController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/* rutas para administrar especialidades */
Route::middleware([SoloAdministrador::class])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/calendario', [AdminDashboardController::class, 'calendario'])->name('dashboard.calendario');

    // administracion de medicos
    Route::get('/medicos', [AdminMedicoController::class, 'index'])->name('medicos.index');
    Route::get('/medicos/crear', [AdminMedicoController::class, 'create'])->name('medicos.create');
    Route::post('/medicos', [AdminMedicoController::class, 'store'])->name('medicos.store');
    Route::get('/medicos/{id}', [AdminMedicoController::class, 'show'])->name('medicos.show');
    Route::get('/medicos/{id}/editar', [AdminMedicoController::class, 'edit'])->name('medicos.edit');
    Route::put('/medicos/{id}', [AdminMedicoController::class, 'update'])->name('medicos.update');
    Route::delete('/medicos/{id}', [AdminMedicoController::class, 'delete'])->name('medicos.delete');

    Route::get('/especialidades', [EspecialidadController::class, 'index'])
        ->name('especialidades.index');

    Route::get('/especialidades/crear', [EspecialidadController::class, 'create'])
        ->name('especialidades.create');

    Route::post('/especialidades', [EspecialidadController::class, 'store'])
        ->name('especialidades.store');

    Route::get('/especialidades/{id}', [EspecialidadController::class, 'show'])
        ->name('especialidades.show');

    Route::get('/especialidades/{id}/editar', [EspecialidadController::class, 'edit'])
        ->name('especialidades.edit');

    Route::put('/especialidades/{id}', [EspecialidadController::class, 'update'])
        ->name('especialidades.update');

    Route::delete('/especialidades/{id}', [EspecialidadController::class, 'delete'])
        ->name('especialidades.delete');
});


/* TURNO */
Route::middleware(['auth'])->group(function () {
    // 7 métodos estándar
    // Route::get('/turnos', [TurnoController::class, 'index'])->name('turnos.index');
    Route::get('/solicitar-turno', [TurnoController::class, 'create'])->name('create.turno');
    Route::post('/confirmar-turno', [TurnoController::class, 'store'])->name('store.turno');
    // Route::get('/turnos/{id}', [TurnoController::class, 'show'])->name('turnos.show');
    Route::get('/turnos/{id}/editar', [TurnoController::class, 'edit'])->name('turnos.edit');
    Route::put('/turnos/{id}', [TurnoController::class, 'update'])->name('turnos.update');
    Route::delete('/turnos/{id}', [TurnoController::class, 'delete'])->name('turnos.delete');

    // Endpoints AJAX para Tom Select y Flatpickr (librerias.js)
    Route::get('/turnos/ajax/medicos/{especialidadId}', [TurnoController::class, 'medicosPorEspecialidad'])
        ->name('turnos.ajax.medicos');
    Route::get('/turnos/ajax/disponibilidad/{medicoId}', [TurnoController::class, 'disponibilidadMedico'])
        ->name('turnos.ajax.disponibilidad');

    Route::get('/turnos/ajax/especialidad/{medicoId}', [TurnoController::class, 'especialidadPorMedico'])
        ->name('turnos.ajax.especialidad');

    // Comprobante PDF
    Route::get('/turnos-{turno}-comprobante', [TurnoController::class, 'descargarComprobante'])
        ->name('turno.comprobante');
});
