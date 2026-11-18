<?php

use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/////////////////////////////////////////////////////////
////////////////rutas de prueba para ver las vistas/////
///////////////los formularios se ven desde la opcion 'conocer equipo'
Route::get('/paciente-panel', function () {
    return view('usuarios.paciente.panel');
})->name('paciente.panel');
Route::get('/paciente-cuenta', function () {
    return view('usuarios.paciente.cuenta');
})->name('paciente.cuenta');
Route::get('/medico-panel', function () {
    return view('usuarios.medico.panel');
})->name('medico.panel');
Route::get('/medico-registro', function () {
    return view('usuarios.medico.registro');
})->name('medico.registro');
Route::get('/medico-cuenta', function () {
    return view('usuarios.medico.cuenta');
})->name('medico.cuenta');
Route::get('/admin-panel', function () {
    return view('usuarios.admin.panel');
})->name('admin.panel');
Route::get('/admin-cuenta', function () {
    return view('usuarios.admin.cuenta');
})->name('admin.cuenta');



/////////////////////////////////////////////////////////
////////////////////////////////////////////////////////


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
