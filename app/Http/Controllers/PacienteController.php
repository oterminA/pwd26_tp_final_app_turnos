<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;

class PacienteController extends Controller
{
    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function mostrarLogin()
    {
        return view('usuarios.paciente.login');
    }

    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function create()
    {
        return view('usuarios.paciente.registro');
    }

    /**
     *
    */
    public function store(){}

    /**
     * funcion que muestra el formulario de edicion de datos
     */
    public function edit()
    {
        $user = auth()->user();
        $paciente = $user->paciente;

        return view('usuarios.paciente.cuenta', compact('user', 'paciente'));
    }

    /**
     * funcion para guardar los datos editados del paciente
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $paciente = $user->paciente;

        $datosValidados = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'password'         => 'nullable|min:8|confirmed',
            'dni'              => 'required|string|unique:pacientes,dni,' . $paciente->id,
            'fecha_nacimiento' => 'required|date',
            'telefono'         => 'required|string|max:50',
            'obra_social'      => 'nullable|string|max:100',
        ]);

        $paciente->updateDatos($datosValidados);

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
}
