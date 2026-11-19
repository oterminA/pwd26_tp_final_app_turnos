<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class TurnoController extends Controller
{
    /**
     * funcion para mostrar el formulario de pedido de turno
     */
    public function create()
    {
        return view('usuarios.turno');
    }

    /**
     * funcion que guarda el turno
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha' => 'required|string|max:255|unique:especialidades,nombre',
            'hora_inicio' => 'required|integer|min:1',
            'hora_fin' => 'required|integer|min:1',
            'estado' => 'required|integer|min:1',
            'codigo' => 'required|integer|min:1',
        ]);

        Turno::create($datos);

        return redirect()->route('usuarios.turno')
            ->with('success', 'Turno agregado correctamente.');
    }


    /**
     * funcion que maneja lo que es la libreria dompdf para descargar el comprobante del turno
     */
    public function descargarComprobante(Turno $turno)
    {
        Gate::authorize('descargarComprobante', $turno); //POLICY DE TURNO, nos aseguramos q el dueño del turno es quien quiere descargarlo

        $turno->load(['paciente.user', 'medico.user', 'especialidad']); //acá hacemos consultas con eager loading para que de una vez se hagan y no tener que ir tres veces a la bd, entonces cargamos todo en la variable $turno para pasarlo ala vista

        $pdf = Pdf::loadView('turnos.comprobante_pdf', compact('turno'));

        return $pdf->stream("comprobante-turno-{$turno->codigo}.pdf"); //esto es para que se abra otra pestaña y ahi descargue el turno, no q se descargue enseguida y pasamos toda la información que la vista necesita para dibujar en el comprobante
    }
}
