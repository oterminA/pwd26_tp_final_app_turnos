<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;

class TurnoController extends Controller
{
    /**
     * funcion para mostrar el formulario de pedido de turno
    */
    public function create(){
        return view('usuarios.turno');

    }

    /**
     * funcion que guarda el turno
    */
    public function store(Request $request){
         $datos = $request->validate([
            'fecha' => 'required|string|max:255|unique:especialidades,nombre',
            'hora_inicio' => 'nullable|string',
            'hora_fin' => 'required|integer|min:1',
            'estado' => 'required|integer|min:1',
            'codigo' => 'required|integer|min:1',
        ]);

        Turno::create($datos);

        return redirect()->route('usuarios.turno')
            ->with('success', 'Turno agregado correctamente.');
    }
}
