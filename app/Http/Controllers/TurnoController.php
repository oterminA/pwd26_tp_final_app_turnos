<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class TurnoController extends Controller
{
    /**
     * 2. CREATE: Muestra el formulario de pedido de turno.
     */
    public function create()
    {
        // Pasamos las especialidades activas para cargar Tom Select
        $especialidades = Especialidad::select('id', 'nombre')->orderBy('nombre')->get();

        return view('usuarios.turno', compact('especialidades'));
    }

    /**
     * 3. STORE: Valida disponibilidad real y guarda el turno en la BD.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->isPaciente() && $user->paciente, 403);

        $datos = $request->validate([
            'medico'       => 'required|integer|exists:medicos,id',
            'especialidad' => 'required|integer|exists:especialidades,id',
            'fecha_hora'   => 'required|date_format:Y-m-d H:i:s',
        ]);

        $inicio = Carbon::createFromFormat('!Y-m-d H:i:s', $datos['fecha_hora']);
        $especialidad = Especialidad::findOrFail($datos['especialidad']);
        $duracion = (int) $especialidad->duracion_turno;

        if ($duracion < 1) {
            throw ValidationException::withMessages([
                'especialidad' => 'Esta especialidad no tiene una duracion de turno valida.',
            ]);
        }

        $fin = $inicio->copy()->addMinutes($duracion);

        if (!$inicio->isFuture() || $inicio->toDateString() !== $fin->toDateString()) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'El turno debe ser futuro y finalizar el mismo dia.',
            ]);
        }

        $medico = Medico::findOrFail($datos['medico']);

        // La obra social informa la cobertura, pero no impide reservar.
        $pacienteId = $user->paciente->id;
        Turno::reservarSiDisponible($medico->id, $especialidad, $inicio, $fin, $pacienteId);

        return redirect()->route('usuarios.paciente.panel')
            ->with('success', 'Turno reservado correctamente.');
    }

    /**
     * 5. EDIT: Muestra el formulario para cambiar estado del turno (solo médico asignado).
     */
    public function edit($id)
    {
        $turno = Turno::findOrFail($id);
        Gate::authorize('cambiarEstado', $turno);

        if (!$turno->puedeCambiarEstado()) {
            return redirect()->route('usuarios.medico.panel')
                ->withErrors(['turno' => 'Este turno todavia no comenzo o ya no permite cambios de estado.']);
        }

        return view('usuarios.edit', compact('turno'));
    }

    /**
     * 6. UPDATE: solo el medico asignado puede marcar finalizado o ausente.
     * La cancelacion tiene su propia accion y sus propias reglas.
     */
    public function update(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);
        Gate::authorize('cambiarEstado', $turno);

        $datos = $request->validate([
            'estado' => 'required|in:finalizado,ausente',
        ]);

        if (!$turno->puedeCambiarEstado($datos['estado'])) {
            throw ValidationException::withMessages([
                'estado' => 'El turno debe estar pendiente y haber comenzado para finalizarlo, o haber terminado para marcarlo ausente.',
            ]);
        }

        $turno->marcarEstado($datos['estado']);

        return redirect()->route('usuarios.medico.panel')
            ->with('success', 'Estado del turno actualizado.');
    }

    /**
     * 7. DELETE: el paciente cancela con al menos 48 horas de anticipacion.
     * El medico asignado puede cancelar sin ese limite.
     */
    public function delete($id)
    {
        $turno = Turno::findOrFail($id);
        Gate::authorize('cancelar', $turno);
        $user = auth()->user();
        $destino = $user->rol === 'medico'
            ? 'usuarios.medico.panel'
            : 'usuarios.paciente.panel';

        if (!$turno->puedeCancelar()) {
            return back()->withErrors([
                'turno' => 'Solo se pueden cancelar turnos pendientes.',
            ]);
        }

        if ($user->rol === 'paciente' && !$turno->puedeCancelarPorPaciente()) {
            return back()->withErrors([
                'turno' => 'Los pacientes deben cancelar con al menos 48 horas de anticipacion.',
            ]);
        }

        $turno->cancelar();

        return redirect()->route($destino)
            ->with('success', 'Turno cancelado correctamente. El horario volvio a quedar disponible.');
    }

    /**
     * ENDPOINT AJAX: Devuelve los médicos de una especialidad,
     * indicando si aceptan la obra social del paciente logueado.
     */
    public function medicosPorEspecialidad($especialidadId)
    {
        $user = auth()->user();

        $obraSocialPaciente = null;
        if ($user && $user->rol === 'paciente' && $user->paciente) {
            $obraSocialPaciente = $user->paciente->obra_social;
        }

        $medicos = Medico::with('user')
            ->whereHas('especialidades', function ($query) use ($especialidadId) {
                $query->where('especialidades.id', $especialidadId);
            })
            ->get()
            ->map(function ($medico) use ($obraSocialPaciente, $user) {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->user->nombre_visible,
                    'apellido' => '',
                    'imagen' => $medico->urlImagen($medico->imagen),
                    'mostrar_cobertura' => $user && $user->rol === 'paciente',
                    'acepta_obra_social' => $medico->aceptaObraSocial($obraSocialPaciente),
                ];
            });

        return response()->json($medicos);
    }

    /**
     * ENDPOINT AJAX: Calcula las fechas y horarios libres de un médico
     * para los próximos 30 días usando Carbon, su duración estándar y turnos activos.
     */
    public function disponibilidadMedico(Request $request, $medicoId)
    {
        $datos = $request->validate([
            'especialidad' => 'required|integer|exists:especialidades,id',
        ]);

        $medico = Medico::with('disponibilidades')->findOrFail($medicoId);
        $especialidad = Especialidad::findOrFail($datos['especialidad']);

        if (!$medico->especialidades()->where('especialidades.id', $especialidad->id)->exists()) {
            return response()->json(['message' => 'El profesional no atiende esta especialidad.'], 422);
        }

        $duracion = (int) $especialidad->duracion_turno;
        if ($duracion < 1) {
            return response()->json(['message' => 'La especialidad no tiene una duracion valida.'], 422);
        }

        $resultado = Turno::horariosDisponibles($medico, $especialidad);
        return response()->json($resultado);
    }

    /**
     * funcion para descargar el comprobante
     */
    public function descargarComprobante(Turno $turno)
    {
        Gate::authorize('descargarComprobante', $turno); //POLICY TURNO donde solo el medico asignado y paciente dueño pueden descargarlo

        $turno->load(['paciente.user', 'medico.user', 'especialidad']);

        $pdf = Pdf::loadView('usuarios.comprobante', compact('turno'));

        return $pdf->stream("comprobante-turno-{$turno->codigo}.pdf");
    }

    /**
     * ajax
     * esta funcion es como la de medicoPorEspecialidad pero la inversa para poder mostrar las especialidades segun el medico
     */
    public function especialidadPorMedico($medicoId)
    {
        $medico = Medico::with('especialidades')->findOrFail($medicoId);

        $especialidades = $medico->especialidades
            ->sortBy('nombre')
            ->values()
            ->map(function ($especialidad) {
                return [
                    'id' => $especialidad->id,
                    'nombre' => $especialidad->nombre,
                ];
            });

        return response()->json($especialidades);
    }
}
