<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    /**
     * funcion para autenticar al usuario que inicia sesion
     */
    public function autenticar(Request $request)
    {
        $request->validate([
            'user' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['user' => $request->user, 'password' => $request->password])) { //si las credenciales están bien
            $request->session()->regenerate();
            return redirect()->intended(route('peliculas.index'));
        }
        return back()->withErrors([ //si las credenciales están mal
            'user' => 'Las credenciales proporcionadas no son válidas.',
        ])->onlyInput('user');
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
