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

/*para el login-registro*/
Route::get('/crear-cuenta', [UserController::class, 'create'])->name('create.cuenta'); //solo para mostrar el form
Route::get('/iniciar-sesion', [UserController::class, 'mostrarLogin'])->name('login');//solo para mostrar el form
