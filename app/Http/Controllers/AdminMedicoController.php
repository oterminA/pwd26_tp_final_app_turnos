<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminMedicoController extends Controller
{
    // lista los medicos activos e inactivos
    public function index()
    {
        $medicos = Medico::withTrashed()->with(['user', 'especialidades'])
            ->orderBy('id', 'desc')->get();

        return view('usuarios.admin.medicos.index', compact('medicos'));
    }

    // muestra el formulario de alta
    public function create()
    {
        $especialidades = Especialidad::orderBy('nombre')->get();
        return view('usuarios.admin.medicos.create', compact('especialidades'));
    }

    // crea el usuario, el medico y sus especialidades
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:80',
            'apellido' => 'required|string|max:80',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'tipoMat' => ['required', Rule::in(array_keys(config('administracion.tipos_matricula')))],
            'numMat' => ['required', 'regex:/^[0-9]{1,10}$/', Rule::unique('medicos', 'numMat')->where('tipoMat', $request->tipoMat)],
            'obras_sociales' => 'required|array|min:1',
            'obras_sociales.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('administracion.obras_sociales')))],
            'especialidades' => 'required|array|min:1',
            'especialidades.*' => ['integer', 'distinct', Rule::exists('especialidades', 'id')->whereNull('deleted_at')],
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // se almacena la foto en el disco publico si se adjunta
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            File::ensureDirectoryExists(public_path('imagenes'));
            $archivo = $request->file('imagen');
            $nombre = Str::uuid() . '.' . $archivo->extension();
            $archivo->move(public_path('imagenes'), $nombre);
            $rutaImagen = 'imagenes/' . $nombre;
        }

        try {
            $medico = DB::transaction(function () use ($datos, $rutaImagen) {
            $user = User::create([
                'name' => trim($datos['nombre'] . ' ' . $datos['apellido']),
                'email' => $datos['email'],
                'password' => $datos['password'], // User lo hashea por casts()
                'rol' => 'medico',
            ]);

            $medico = Medico::create([
                'user_id' => $user->id,
                'tipoMat' => $datos['tipoMat'],
                'numMat' => $datos['numMat'],
                'matricula' => $datos['tipoMat'] . '-' . $datos['numMat'],
                'obra_social' => $datos['obras_sociales'][0], // valor anterior para compatibilidad
                'obras_sociales' => $datos['obras_sociales'],
                'imagen' => $rutaImagen,
            ]);
            $medico->especialidades()->sync($datos['especialidades']);
            return $medico;
        });

        } catch (\Throwable $e) {
            if ($rutaImagen) File::delete(public_path($rutaImagen));
            throw $e;
        }

        Log::info('Admin creo medico', ['admin_id' => auth()->id(), 'medico_id' => $medico->id]);
        return redirect()->route('admin.medicos.index')->with('success', 'Medico agregado correctamente.');
    }

    // muestra el detalle, incluso si esta inactivo
    public function show($id)
    {
        $medico = Medico::withTrashed()->with(['user', 'especialidades'])->findOrFail($id);
        $turnosMes = $medico->turnos()->whereYear('fecha', now()->year)->whereMonth('fecha', now()->month)->count();
        $turnosFinalizados = $medico->turnos()->whereYear('fecha', now()->year)->whereMonth('fecha', now()->month)->where('estado', 'finalizado')->count();
        $proximosTurnos = $medico->turnos()->with(['paciente.user', 'especialidad'])
            ->whereDate('fecha', '>=', now()->toDateString())
            ->whereNotIn('estado', ['cancelado', 'finalizado', 'ausente'])
            ->orderBy('fecha')->orderBy('hora_inicio')->limit(5)->get();
        return view('usuarios.admin.medicos.show', compact('medico', 'turnosMes', 'turnosFinalizados', 'proximosTurnos'));
    }

    // muestra el formulario de edicion
    public function edit($id)
    {
        $medico = Medico::withTrashed()->with(['user', 'especialidades'])->findOrFail($id);
        $especialidades = Especialidad::orderBy('nombre')->get();
        return view('usuarios.admin.medicos.edit', compact('medico', 'especialidades'));
    }

    // actualiza los datos de ambas tablas y las relaciones
    public function update(Request $request, $id)
    {
        $medico = Medico::withTrashed()->with('user')->findOrFail($id);
        $datos = $request->validate([
            'nombre' => 'required|string|max:80',
            'apellido' => 'required|string|max:80',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($medico->user_id)],
            'password' => 'nullable|string|min:8|confirmed',
            'tipoMat' => ['required', Rule::in(array_keys(config('administracion.tipos_matricula')))],
            'numMat' => ['required', 'regex:/^[0-9]{1,10}$/', Rule::unique('medicos', 'numMat')->where('tipoMat', $request->tipoMat)->ignore($medico->id)],
            'obras_sociales' => 'required|array|min:1',
            'obras_sociales.*' => ['required', 'string', 'distinct', Rule::in(array_keys(config('administracion.obras_sociales')))],
            'especialidades' => 'required|array|min:1',
            'especialidades.*' => ['integer', 'distinct', Rule::exists('especialidades', 'id')->whereNull('deleted_at')],
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'estado' => 'required|in:activo,inactivo',
        ]);

        if ($datos['estado'] === 'inactivo' && !$medico->trashed() && $this->tieneTurnosFuturos($medico)) {
            return back()->withInput()->with('error', 'No se puede desactivar un medico con turnos futuros pendientes.');
        }

        $rutaAnterior = $medico->imagen;
        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            File::ensureDirectoryExists(public_path('imagenes'));
            $archivo = $request->file('imagen');
            $nombre = Str::uuid() . '.' . $archivo->extension();
            $archivo->move(public_path('imagenes'), $nombre);
            $rutaImagen = 'imagenes/' . $nombre;
        }
        try {
            DB::transaction(function () use ($datos, $medico, $rutaImagen) {
            $userDatos = [
                'name' => trim($datos['nombre'] . ' ' . $datos['apellido']),
                'email' => $datos['email'],
            ];
            if (!empty($datos['password'])) {
                $userDatos['password'] = $datos['password'];
            }
            $medico->user->update($userDatos);

            $medico->update([
                'tipoMat' => $datos['tipoMat'],
                'numMat' => $datos['numMat'],
                'matricula' => $datos['tipoMat'] . '-' . $datos['numMat'],
                'obra_social' => $datos['obras_sociales'][0], // valor anterior para compatibilidad
                'obras_sociales' => $datos['obras_sociales'],
                'imagen' => $rutaImagen ?? $medico->imagen,
            ]);
            $medico->especialidades()->sync($datos['especialidades']);

            if ($datos['estado'] === 'inactivo' && !$medico->trashed()) {
                $medico->delete();
            } elseif ($datos['estado'] === 'activo' && $medico->trashed()) {
                $medico->restore();
            }
        });

        } catch (\Throwable $e) {
            if ($rutaImagen) File::delete(public_path($rutaImagen));
            throw $e;
        }
        if ($rutaImagen && $rutaAnterior && str_starts_with($rutaAnterior, 'imagenes/')) {
            File::delete(public_path($rutaAnterior));
        }

        Log::info('Admin modifico medico', ['admin_id' => auth()->id(), 'medico_id' => $medico->id, 'estado' => $datos['estado']]);
        return redirect()->route('admin.medicos.index')->with('success', 'Medico actualizado correctamente.');
    }

    // baja logica, nunca elimina el usuario ni los turnos
    public function delete($id)
    {
        $medico = Medico::findOrFail($id);
        if ($this->tieneTurnosFuturos($medico)) {
            return back()->with('error', 'No se puede desactivar un medico con turnos futuros pendientes.');
        }
        $medico->delete();
        Log::info('Admin desactivo medico', ['admin_id' => auth()->id(), 'medico_id' => $medico->id]);
        return redirect()->route('admin.medicos.index')->with('success', 'Medico desactivado correctamente.');
    }

    private function tieneTurnosFuturos(Medico $medico): bool
    {
        return $medico->turnos()
            ->whereDate('fecha', '>=', now()->toDateString())
            ->whereNotIn('estado', ['cancelado', 'finalizado', 'ausente'])
            ->exists();
    }
}
