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

Route::get('/solicitar-turno', function () {
    return view('usuarios.turno');
})->name('turno');

/*para el login-registro de PACIENTE*/
Route::get('/crear-cuenta-paciente', [PacienteController::class, 'create'])->name('create.paciente');
Route::get('/iniciar-sesion-paciente', [PacienteController::class, 'mostrarLogin'])->name('login.paciente');


/*para el login-registro de MEDICO*/
Route::get('/iniciar-sesion-profesional', [MedicoController::class, 'mostrarLogin'])->name('login.medico');

/*para el login-registro de ADMIN*/
Route::get('/iniciar-sesion-admin', function () {
    return view('usuarios.admin.login');
})->name('login.admin');
