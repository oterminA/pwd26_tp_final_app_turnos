<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'         => 'nullable|string|min:8|confirmed',
            'tipoMat' => 'prohibited',
            'numMat' => 'prohibited',
            'matricula' => 'prohibited',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'obras_sociales' => 'required|array|min:1',
            'obras_sociales.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('administracion.obras_sociales')))],
            'especialidades' => 'required|array|min:1',
            'especialidades.*' => ['integer', 'distinct', Rule::exists('especialidades', 'id')->whereNull('deleted_at')],
        ], [
            'tipoMat.prohibited' => 'No tenés permiso para modificar la matrícula desde este formulario.',
            'numMat.prohibited' => 'No tenés permiso para modificar la matrícula desde este formulario.',
            'matricula.prohibited' => 'No tenés permiso para modificar la matrícula desde este formulario.',
        ]);

        $medico->validarEspecialidades($datosValidados['especialidades']);
        $rutaAnterior = $medico->imagen;
        $rutaNueva = null;
        if ($request->hasFile('imagen')) {
            File::ensureDirectoryExists(public_path('imagenes'));
            $archivo = $request->file('imagen');
            $nombre = Str::uuid() . '.' . $archivo->extension();
            $archivo->move(public_path('imagenes'), $nombre);
            $rutaNueva = 'imagenes/' . $nombre;
            $datosValidados['imagen'] = $rutaNueva;
        }

        try {
            $medico->updateDatos($datosValidados);
        } catch (\Throwable $e) {
            if ($rutaNueva) File::delete(public_path($rutaNueva));
            throw $e;
        }
        if ($rutaNueva && $rutaAnterior && str_starts_with($rutaAnterior, 'imagenes/')) {
            File::delete(public_path($rutaAnterior));
        }

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
}
