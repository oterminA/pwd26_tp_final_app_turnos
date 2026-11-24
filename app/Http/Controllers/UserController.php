<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    /**
     * funcion para autenticar al usuario que inicia sesion (acá)
     */
    public function autenticar(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales) && $request->user()->rol === 'admin') {
            $request->session()->regenerate();
            $respuesta = redirect()->intended(route('usuarios.admin.panel'));
        } else {
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $respuesta = back()->withErrors([
                'email' => 'Las credenciales proporcionadas no son válidas.',
            ])->onlyInput('email');
        }

        return $respuesta;
    }


    /**
     * funcion que manda panel de admin
     */
    public function panel()
    {
        return view('usuarios.admin.panel');
    }


    /**
     * funcion para cerrar sesion, la usan los tres roles
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * funcion que manda al formulario donde el admin edita sus datos
     */
    public function edit()
    {
        $user = auth()->user();
        return view('usuarios.admin.cuenta', compact('user'));
    }

    /**
     * funcion para editar los datos del admin que no tiene controller propio
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $datos = $request->validate([
            'name'  => 'required| string|min:6|max:255|regex:/^[\pL\s]+$/u',
            'email' => 'required|email|unique:user,email,' . $user->id,
        ]);

        $user->update($datos);

        return redirect()->back()->with('success', 'Tus datos fueron actualizados correctamente.');
    }
}
