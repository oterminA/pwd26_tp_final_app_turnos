<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medico;


class MedicoController extends Controller
{
    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function mostrarLogin()
    {
        return view('usuarios.medico.login');
    }

}
