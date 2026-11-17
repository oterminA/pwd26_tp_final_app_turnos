<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    // muestra todas las especialidades
    public function index()
    {
        $especialidades = Especialidad::withTrashed()
            ->orderBy('nombre')
            ->get();

        return view('usuarios.admin.especialidades.index', compact('especialidades'));
    }

    // muestra el formulario para agregar una especialidad
    public function create()
    {
        return view('usuarios.admin.especialidades.create');
    }

    // guarda una especialidad nueva
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'duracion_turno' => 'required|integer|min:1',
        ]);

        // busca si ya existe una especialidad con ese nombre
        $especialidad = Especialidad::withTrashed()
            ->where('nombre', $datos['nombre'])
            ->first();

        if ($especialidad) {
            if ($especialidad->trashed()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Esta especialidad ya existe pero esta inactiva. Podes reactivarla desde el listado.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe una especialidad con ese nombre.');
        }

        Especialidad::create($datos);

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad agregada correctamente.');
    }

    // muestra los datos de una especialidad
    public function show($id)
    {
        $especialidad = Especialidad::withTrashed()->findOrFail($id);

        return view('usuarios.admin.especialidades.show', compact('especialidad'));
    }

    // muestra el formulario para editar una especialidad
    public function edit($id)
    {
        $especialidad = Especialidad::withTrashed()->findOrFail($id);

        return view('usuarios.admin.especialidades.edit', compact('especialidad'));
    }

    // guarda los cambios de una especialidad
    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::withTrashed()->findOrFail($id);

        $datos = $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre,' . $especialidad->id,
            'descripcion' => 'nullable|string',
            'duracion_turno' => 'required|integer|min:1',
            'estado' => 'required|in:activa,inactiva',
        ]);

        // controla que no tenga turnos antes de desactivarla
        if ($datos['estado'] == 'inactiva' && !$especialidad->trashed()) {
            if ($especialidad->turnos()->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede desactivar la especialidad porque tiene turnos asociados.');
            }
        }

        // actualiza los datos
        $especialidad->nombre = $datos['nombre'];
        $especialidad->descripcion = $datos['descripcion'] ?? null;
        $especialidad->duracion_turno = $datos['duracion_turno'];
        $especialidad->save();

        // desactiva la especialidad
        if ($datos['estado'] == 'inactiva' && !$especialidad->trashed()) {
            $especialidad->delete();
        }

        // reactiva la especialidad
        if ($datos['estado'] == 'activa' && $especialidad->trashed()) {
            $especialidad->restore();
        }

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad modificada correctamente.');
    }

    // desactiva una especialidad si no tiene turnos
    public function delete($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        // verifica si tiene turnos asociados
        if ($especialidad->turnos()->exists()) {
            return redirect()->route('admin.especialidades.index')
                ->with('error', 'No se puede desactivar la especialidad porque tiene turnos asociados.');
        }

        // realiza el borrado logico
        $especialidad->delete();

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad desactivada correctamente.');
    }
}
