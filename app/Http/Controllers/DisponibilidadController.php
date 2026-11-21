<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DisponibilidadController extends Controller
{
    // Listado de días válidos para mantener consistencia con Carbon más adelante
    private const DIAS_SEMANA = [
        'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'
    ];

    /**
     * 1. INDEX: Lista todas las disponibilidades del médico logueado.
     */
    public function index()
    {
        $medico = auth()->user()->medico;
        abort_unless($medico, 403, 'Acceso exclusivo para profesionales.');

        // Traemos solo las franjas horarias del médico actual
        $disponibilidades = $medico->disponibilidades()
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return view('usuarios.medico.disponibilidades.index', compact('disponibilidades'));
    }

    /**
     * 2. CREATE: Muestra el formulario para cargar una nueva franja horaria.
     */
    public function create()
    {
        $dias = self::DIAS_SEMANA;

        return view('usuarios.medico.disponibilidades.create', compact('dias'));
    }

    /**
     * 3. STORE: Valida y guarda la nueva disponibilidad en la base de datos.
     */
    public function store(Request $request)
    {
        $medico = auth()->user()->medico;

        // Validamos día, formato de hora y que hora_fin sea mayor a hora_inicio
        $datos = $request->validate([
            'dia_semana'  => 'required|in:' . implode(',', self::DIAS_SEMANA),
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        ]);

        // Controlamos que no se superponga con otra franja del mismo médico ese día
        $seSuperpone = $medico->disponibilidades()
            ->where('dia_semana', $datos['dia_semana'])
            ->where('hora_inicio', '<', $datos['hora_fin'])
            ->where('hora_fin', '>', $datos['hora_inicio'])
            ->exists();

        if ($seSuperpone) {
            return back()
                ->withInput()
                ->withErrors(['hora_inicio' => 'Esa franja horaria se superpone con una disponibilidad ya cargada.']);
        }

        // Guardamos asociada al médico logueado
        $medico->disponibilidades()->create($datos);

        return redirect()->route('medico.disponibilidades.index')
            ->with('success', 'Disponibilidad agregada correctamente.');
    }

    /**
     * 4. SHOW: Muestra el detalle de una disponibilidad puntual.
     */
    public function show($id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        // Verificamos con la Policy que pertenezca al médico logueado
        Gate::authorize('update', $disponibilidad);

        return view('usuarios.medico.disponibilidades.show', compact('disponibilidad'));
    }

    /**
     * 5. EDIT: Muestra el formulario para editar una disponibilidad existente.
     */
    public function edit($id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        // Policy: solo el médico dueño puede editarla
        Gate::authorize('update', $disponibilidad);

        $dias = self::DIAS_SEMANA;

        return view('usuarios.medico.disponibilidades.edit', compact('disponibilidad', 'dias'));
    }

    /**
     * 6. UPDATE: Actualiza la disponibilidad en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        // Policy: solo el médico dueño puede modificarla
        Gate::authorize('update', $disponibilidad);

        $datos = $request->validate([
            'dia_semana'  => 'required|in:' . implode(',', self::DIAS_SEMANA),
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        ]);

        // Controlamos superposición excluyendo el registro que estamos editando
        $seSuperpone = auth()->user()->medico->disponibilidades()
            ->where('id', '!=', $disponibilidad->id)
            ->where('dia_semana', $datos['dia_semana'])
            ->where('hora_inicio', '<', $datos['hora_fin'])
            ->where('hora_fin', '>', $datos['hora_inicio'])
            ->exists();

        if ($seSuperpone) {
            return back()
                ->withInput()
                ->withErrors(['hora_inicio' => 'Esa franja horaria se superpone con otra disponibilidad tuya.']);
        }

        $disponibilidad->update($datos);

        return redirect()->route('medico.disponibilidades.index')
            ->with('success', 'Disponibilidad actualizada correctamente.');
    }

    /**
     * 7. DELETE: Elimina una disponibilidad del médico.
     */
    public function delete($id)
    {
        $disponibilidad = Disponibilidad::findOrFail($id);

        // Policy: solo el médico dueño puede eliminarla
        Gate::authorize('delete', $disponibilidad);

        $disponibilidad->delete();

        return redirect()->route('medico.disponibilidades.index')
            ->with('success', 'Disponibilidad eliminada correctamente.');
    }
}