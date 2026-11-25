<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MedicoController extends Controller
{

    /**
     * index para mostrar los medicos en la pagina publica
     */
    public function index()
    {
        $medicos = Medico::equipoPublico()->get(); //o sea el scrope con la query para no mexclar responsabilidades del mvc

        return view('publica.equipo', compact('medicos'));
    }

    /**
     * funcion que manda al formulario de inicio de sesion desde el de crear cuenta
     */
    public function mostrarLogin()
    {
        return view('usuarios.medico.login');
    }


    /**
     * funcion para autenticar al usuario que inicia sesion
     */
    public function autenticar(Request $request)
    {
        $datos = $request->validate([
            'tipoMat'  => ['required', 'string'],
            'numMat'   => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
        $matricula = strtoupper(trim($datos['tipoMat'])) . '-' . trim($datos['numMat']);

        $medico = Medico::with('user')
            ->where('matricula', $matricula)
            ->first();
        if (
            !$medico ||
            !$medico->user ||
            $medico->user->rol !== 'medico' ||
            !Hash::check($datos['password'], $medico->user->password)
        ) {
            return back()
                ->withInput($request->only('tipoMat', 'numMat'))
                ->withErrors([
                    'numMat' => 'Las credenciales ingresadas no son válidas.',
                ]);
        }

        Auth::login($medico->user);
        $request->session()->regenerate();

        return redirect()->intended(route('usuarios.medico.panel'));
    }


    /**
     * funcion que manda panel de medico
     */
    public function panel()
    {
        $user = auth()->user();
        $medico = $user?->medico;

        $turnos = $medico
            ? $medico->turnos()
            ->with(['paciente.user', 'especialidad'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get()
            : collect();
        return view('usuarios.medico.panel', compact('medico', 'turnos'));
    }


    /**
     * funcion que muestra el formulario de edicion de datos
     */
    public function edit()
    {
        $user = auth()->user();
        $medico = $user->medico;

        $especialidades = Especialidad::orderBy('nombre')->get();
        $seleccionadas = $medico
            ? $medico->especialidades()->pluck('especialidades.id')->all()
            : [];
        $obrasSeleccionadas = $medico ? $medico->obrasSocialesAsignadas() : [];
        return view('usuarios.medico.cuenta', compact(
            'user',
            'medico',
            'especialidades',
            'seleccionadas',
            'obrasSeleccionadas'
        ));
    }

    /**
     * funcion para guardar los datos editados del medico
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $medico = $user->medico;

        //usamos la regla prohibited que valida que un campo no esté presente o esté vacío en los datos enviados en la solicitud HTTP, laravel da error si se reciben esos campos
        $datosValidados = $request->validate([
            'nombre'           => 'required|string|max:120',
            'apellido'         => 'required|string|max:120',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'password'         => 'nullable|min:8|confirmed',
            'tipoMat'          => 'prohibited',
            'numMat'           => 'prohibited',
            'matricula'        => 'prohibited',
            'imagen'           => 'nullable|mimes:jpg,jpeg,png,webp|max:2048',
            'obras_sociales'   => 'nullable|array',
            'obras_sociales.*' => 'string',
            'especialidades'   => 'nullable|array',
            'especialidades.*' => 'integer|exists:especialidades,id',
        ], [
            'tipoMat.prohibited'   => 'No tenés permiso para modificar la matrícula desde este formulario.',
            'numMat.prohibited'    => 'No tenés permiso para modificar la matrícula desde este formulario.',
            'matricula.prohibited' => 'No tenés permiso para modificar la matrícula desde este formulario.',
        ]);

        //dejamos la matricula original del medico porque es un dato importante
        $datosValidados['matricula'] = $medico->matricula;

        if ($request->hasFile('imagen')) {
            if ($medico->imagen && File::exists(public_path($medico->imagen))) {
                File::delete(public_path($medico->imagen));
            }

            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('imagenes'), $nombreArchivo);

            $datosValidados['imagen'] = 'imagenes/' . $nombreArchivo;
        }

        $medico->updateDatos($datosValidados); //mandamos los datos a que se validen en la transaccion del modelo

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
}
