<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function mostrarLogin()
    {
        return view('usuarios.login');
    }

    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function create()
    {
        return view('usuarios.registro');
    }
}
