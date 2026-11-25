<?php

namespace App\Http\Controllers;

use App\Models\Disponibilidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DisponibilidadController extends Controller
{
    // Listado de días válidos para mantener consistencia con Carbon más adelante
    // private const DIAS_SEMANA = [
    //     'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'
    // ];
    private const DIAS_SEMANA = [
    1 => 'lunes',
    2 => 'martes',
    3 => 'miercoles',
    4 => 'jueves',
    5 => 'viernes',
    6 => 'sabado',
    7 => 'domingo',
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
            ->with('especialidad')
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
        $medico = auth()->user()->medico;
        abort_unless($medico, 403);
        $especialidades = $medico->especialidades()->orderBy('nombre')->get();
        $dias = self::DIAS_SEMANA;

        return view('usuarios.medico.disponibilidades.create', compact('dias', 'especialidades'));
    }

    /**
     * 3. STORE: Valida y guarda la nueva disponibilidad en la base de datos.
     */
    public function store(Request $request)
    {
        $medico = auth()->user()->medico;
        abort_unless($medico, 403);

        // Validamos día, formato de hora y que hora_fin sea mayor a hora_inicio
        $datos = $request->validate([
            // 'dia_semana'  => 'required|in:' . implode(',', self::DIAS_SEMANA),
            'especialidad_id' => ['required', 'integer', Rule::exists('especialidad_medico', 'especialidad_id')->where('medico_id', $medico->id)],
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        ]);

        // Controlamos que no se superponga con otra franja del mismo médico ese día
        $seSuperpone = Disponibilidad::haySuperposicion(
            $medico->id,
            $datos['dia_semana'],
            $datos['hora_inicio'],
            $datos['hora_fin']
        );

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
        $disponibilidad = Disponibilidad::with('especialidad')->findOrFail($id);

        // Verificamos con la Policy que pertenezca al médico logueado
        Gate::authorize('update', $disponibilidad);

        return view('usuarios.medico.disponibilidades.show', compact('disponibilidad'));
    }

    /**
     * 5. EDIT: Muestra el formulario para editar una disponibilidad existente.
     */
    public function edit($id)
    {
        $disponibilidad = Disponibilidad::with('especialidad')->findOrFail($id);

        // Policy: solo el médico dueño puede editarla
        Gate::authorize('update', $disponibilidad);

        $dias = self::DIAS_SEMANA;
        $especialidades = auth()->user()->medico->especialidades()->orderBy('nombre')->get();

        return view('usuarios.medico.disponibilidades.edit', compact('disponibilidad', 'dias', 'especialidades'));
    }

    /**
     * 6. UPDATE: Actualiza la disponibilidad en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $disponibilidad = Disponibilidad::with('especialidad')->findOrFail($id);

        // Policy: solo el médico dueño puede modificarla
        Gate::authorize('update', $disponibilidad);

        $datos = $request->validate([
            // 'dia_semana'  => 'required|in:' . implode(',', self::DIAS_SEMANA),
            'especialidad_id' => ['required', 'integer', Rule::exists('especialidad_medico', 'especialidad_id')->where('medico_id', auth()->user()->medico->id)],
            'dia_semana' => 'required|integer|between:1,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
        ]);

        // Controlamos superposición excluyendo el registro que estamos editando
        $seSuperpone = Disponibilidad::haySuperposicion(
            auth()->user()->medico->id,
            $datos['dia_semana'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $disponibilidad->id
        );

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
        $disponibilidad = Disponibilidad::with('especialidad')->findOrFail($id);

        // Policy: solo el médico dueño puede eliminarla
        Gate::authorize('delete', $disponibilidad);

        $disponibilidad->delete();

        return redirect()->route('medico.disponibilidades.index')
            ->with('success', 'Disponibilidad eliminada correctamente.');
    }
}
