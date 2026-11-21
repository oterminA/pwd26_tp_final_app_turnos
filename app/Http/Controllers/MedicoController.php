<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class MedicoController extends Controller
{
    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function mostrarLogin()
    {
        return view('usuarios.medico.login');
    }

    /**
     * funcion que muestra el formulario de edicion de datos
     */
    public function edit()
    {
        $user = auth()->user();
        $medico = $user->medico;

        return view('usuarios.medico.cuenta', compact('user', 'medico'));
    }

    /**
     * funcion para guardar los datos editados del medico
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $medico = $user->medico;

        $datosValidados = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'password'         => 'nullable|min:8|confirmed',
            'matricula'              => 'required|string|unique:medicos,matricula,' . $medico->id,
            'imagen' => 'nullable|mimes:jpg,jpeg,png,webp|max:300',
            'obra_social'      => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('imagen')) {
            if ($medico->imagen && File::exists(public_path($medico->imagen))) {
                File::delete(public_path($medico->imagen));
            }

            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('imagenes'), $nombreArchivo);

            $validated['imagen'] = 'imagenes/' . $nombreArchivo;
        }

        $medico->updateDatos($datosValidados);

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
    public function mostrarConfigurarTurno()
{
    $medico = auth()->user()->medico;
    abort_unless($medico, 403, 'Acceso exclusivo para profesionales.');

    // Si ya configuró la duración, lo mandamos a su cuenta/panel
    if (!is_null($medico->duracion_turno)) {
        return redirect()->route('medico.edit');
    }

    return view('usuarios.medico.configurar-turno');
}

public function guardarDuracionTurno(\Illuminate\Http\Request $request)
{
    $request->validate([
        'duracion_turno' => 'required|integer|in:15,20,30,45,60',
    ]);

    $medico = auth()->user()->medico;
    $medico->update([
        'duracion_turno' => $request->duracion_turno,
    ]);

    return redirect()->route('medico.edit')
        ->with('success', 'Duración estándar de turnos configurada correctamente.');
}
}
