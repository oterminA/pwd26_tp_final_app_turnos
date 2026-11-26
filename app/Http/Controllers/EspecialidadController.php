<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    // muestra todas las especialidades
    public function index()
    {
        $especialidades = Especialidad::withTrashed()
            ->orderBy('nombre')
            ->get();

        $activas = $especialidades->filter(fn ($e) => !$e->trashed())->count();
        return view('usuarios.admin.especialidades.index', compact('especialidades', 'activas'));
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

        $nueva = Especialidad::create($datos);
        Log::info('Admin creo especialidad', ['admin_id' => auth()->id(), 'especialidad_id' => $nueva->id]);

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

        $especialidad->actualizarDatos($datos);

        Log::info('Admin modifico especialidad', ['admin_id' => auth()->id(), 'especialidad_id' => $especialidad->id, 'estado' => $datos['estado']]);
        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad modificada correctamente.');
    }

    // desactiva una especialidad si no tiene turnos
    public function delete($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        // verifica si tiene turnos asociados
        if (!$especialidad->puedeDesactivarse()) {
            return redirect()->route('admin.especialidades.index')
                ->with('error', 'No se puede desactivar la especialidad porque tiene turnos pendientes.');
        }

        // realiza el borrado logico
        $especialidad->delete();
        Log::info('Admin desactivo especialidad', ['admin_id' => auth()->id(), 'especialidad_id' => $especialidad->id]);

        return redirect()->route('admin.especialidades.index')
            ->with('success', 'Especialidad desactivada correctamente.');
    }
}
