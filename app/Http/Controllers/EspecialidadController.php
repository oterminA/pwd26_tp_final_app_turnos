<?php
namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    // muestra todas las especialidades
    public function index()
    {
        $especialidades = Especialidad::orderBy('nombre')->get();

        return view('admin.especialidades.index', compact('especialidades'));
    }

    // muestra el formulario para agregar una especialidad
    public function create()
    {
        return view('admin.especialidades.create');
    }

    // guarda una especialidad nueva
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre',
            'descripcion' => 'nullable|string',
            'duracion_turno' => 'required|integer|min:1',
        ]);

        Especialidad::create($datos);

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad agregada correctamente.');
    }

    // muestra los datos de una especialidad
    public function show($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        return view('admin.especialidades.show', compact('especialidad'));
    }

    // muestra el formulario para editar una especialidad
    public function edit($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        return view('admin.especialidades.edit', compact('especialidad'));
    }

    // guarda los cambios de una especialidad
    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        $datos = $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre,' . $especialidad->id,
            'descripcion' => 'nullable|string',
            'duracion_turno' => 'required|integer|min:1',
        ]);

        $especialidad->update($datos);

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad modificada correctamente.');
    }

    // se completara cuando agreguemos el borrado logico
    public function delete($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        if ($especialidad->turnos()->exists()) {
            return redirect()->route('admin.especialidades.index')
                ->with('error', 'No se puede desactivar la especialidad porque tiene turnos asociados.');
        }

        return redirect()->route('admin.especialidades.index')
            ->with('error', 'Todavia falta configurar el borrado logico para desactivar especialidades.');
    }
}
