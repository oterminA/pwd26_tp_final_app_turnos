<?php

use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*para la landing page*/

Route::get('/', function () {
    return view('publica.landing');
});
Route::get('/equipo', function () {
    return view('publica.equipo');
})->name('show.equipo');


/*PACIENTE*/
Route::get('/registrarse-pacinte', [PacienteController::class, 'create'])->name('create.paciente');

Route::post('/guardar-paciente', [PacienteController::class, 'store'])->name('store.paciente');


Route::get('/iniciar-sesion-paciente', [PacienteController::class, 'mostrarLogin'])->name('login.paciente');

Route::put('/mi-cuenta-paciente', [PacienteController::class, 'update'])->name('paciente.update');

Route::get('/mi-cuenta-paciente', [PacienteController::class, 'edit'])->name('paciente.edit');

Route::post('/ingresar-paciente', [UserController::class, 'autenticar'])->name('paciente.autenticar');


/*MEDICO*/
Route::get('/iniciar-sesion-profesional', [MedicoController::class, 'mostrarLogin'])->name('login.medico');

Route::put('/mi-cuenta-profesional', [MedicoController::class, 'update'])->name('medico.update');

Route::get('/mi-cuenta-profesional', [MedicoController::class, 'edit'])->name('medico.edit');

Route::post('/ingresar-profesional', [UserController::class, 'autenticar'])->name('medico.autenticar');



/*ADMIN*/
Route::get('/iniciar-sesion-admin', function () {
    return view('usuarios.admin.login');
})->name('login.admin');

Route::get('/mi-cuenta-admin', [UserController::class, 'edit'])->name('admin.edit');

Route::put('/mi-cuenta-admin', [UserController::class, 'update'])->name('admin.update');

Route::post('/ingresar-admin', [UserController::class, 'autenticar'])->name('admin.autenticar');



/*para el cerrar sesion de todos los roles*/
Route::post('/logout', [UserController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/* rutas para administrar especialidades */
Route::prefix('admin')->name('admin.')->group(function () {

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
Route::get('/solicitar-turno', [TurnoController::class, 'create'])->name('create.turno');

Route::post('/confirmar-turno', [TurnoController::class, 'store'])->name('store.turno');

Route::middleware(['auth'])->group(function () {
    Route::get('/turnos-{turno}-comprobante', [TurnoController::class, 'descargarComprobante'])
        ->name('turno.comprobante'); //para que el paciente pueda descargar el comprobante del turno
});
