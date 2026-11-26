<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * funcion para autenticar al usuario que inicia sesion
     */
    public function autenticar(Request $request)
    {
        $request->validate([
            'dni' => ['required', 'digits_between:7,8'],
            'password' => ['required'],
        ]);

        $paciente = Paciente::with('user')
            ->where('dni', $request->dni)
            ->first();

        if ($paciente && $paciente->user && Hash::check($request->password, $paciente->user->password)) {
            Auth::login($paciente->user);
            $request->session()->regenerate();

            $respuesta = redirect()->intended(route('usuarios.paciente.panel'));
        } else {
            $respuesta = back()->withErrors([
                'dni' => 'Las credenciales proporcionadas no son válidas.',
            ])->onlyInput('dni');
        }

        return $respuesta;
    }


    /**
     * funcion que manda panel de paciente
     */
    public function panel()
    {
        $paciente = Auth::user()->paciente;

        $turnos = $paciente
            ? $paciente->turnos()->with(['medico.user', 'especialidad'])->orderByDesc('fecha')->orderByDesc('hora_inicio')->get()
            : collect();

        return view('usuarios.paciente.panel', compact('turnos'));
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
    public function store(Request $request)
    {
        $datos = $request->validate([ //se validan los datos que vienen del formulario de registro
            'nombre'           => 'required|string|max:120',
            'apellido'         => 'required|string|max:120',
            'email'            => 'required|email|max:255|unique:users,email',
            'password'         => 'required|string|min:8|confirmed',
            'dni'              => 'required|digits_between:7,8|unique:pacientes,dni',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'telefono'         => 'required|string|max:50',
            'obra_social'      => ['nullable', 'string', Rule::in(array_keys(config('administracion.obras_sociales')))],
        ]);

        $paciente = new Paciente();
        $user = $paciente->createDatos($datos); //esos datos se mandan al modelo para hacer la db::Transaction que es que en user y paciente se crea el usuario al mismo tiempo

        Auth::login($user); //se loguea el usuario
        $request->session()->regenerate();

        return redirect()->route('usuarios.paciente.panel') //se redirige al panel de usuario
            ->with('success', '¡Cuenta creada!');
    }


    /**
     * funcion que muestra el formulario de edicion de datos
     */
    public function edit()
    {
        $user = auth()->user();
        $paciente = $user->paciente;
        $obrasSeleccionadas = $paciente ? $paciente->obrasSocialesAsignadas() : [];

        return view('usuarios.paciente.cuenta', compact('user', 'paciente', 'obrasSeleccionadas'));
    }

    /**
     * funcion para guardar los datos editados del paciente
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $paciente = $user->paciente;

        $datosValidados = $request->validate([
            'nombre'           => 'required|string|max:120',
            'apellido'         => 'required|string|max:120',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'         => 'nullable|min:8|confirmed',
            'dni'              => 'required|digits_between:7,8|unique:pacientes,dni,' . $paciente->id,
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'telefono'         => 'required|string|max:50',
            'obra_social'      => ['nullable', 'string', Rule::in(array_keys(config('administracion.obras_sociales')))],
        ]);

        $paciente->updateDatos($datosValidados);

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
}
