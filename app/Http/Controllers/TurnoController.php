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
    // Cantidad mínima de días de anticipación requeridos para cancelar un turno
    private const DIAS_MINIMOS_CANCELACION = 2;

    /**
     * 1. INDEX: Lista los turnos del paciente o del médico logueado.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->rol === 'paciente' && $user->paciente) {
            $turnos = $user->paciente->turnos()
                ->with(['medico.user', 'especialidad'])
                ->orderBy('fecha', 'desc')
                ->orderBy('hora_inicio', 'desc')
                ->get();
        } elseif ($user->rol === 'medico' && $user->medico) {
            $turnos = $user->medico->turnos()
                ->with(['paciente.user', 'especialidad'])
                ->orderBy('fecha', 'desc')
                ->orderBy('hora_inicio', 'desc')
                ->get();
        } else {
            $turnos = Turno::with(['paciente.user', 'medico.user', 'especialidad'])
                ->orderBy('fecha', 'desc')
                ->get();
        }

        return view('usuarios.turnos.index', compact('turnos'));
    }

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
        $datos = $request->validate([
            'medico'       => 'required|integer|exists:medicos,id',
            'especialidad' => 'required|integer|exists:especialidades,id',
            'fecha_hora'   => 'required|date_format:Y-m-d H:i:s',
        ]);

        $user = auth()->user();
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

        // Por ahora se mantiene la regla de obras sociales de la version actual.
        // La vamos a unificar en la siguiente correccion.
        if ($user->rol === 'paciente' && $user->paciente) {
            $coberturas = $medico->obrasSocialesAsignadas();
            $obraSocialPaciente = $user->paciente->obra_social;
            if (!$obraSocialPaciente || !in_array($obraSocialPaciente, $coberturas, true)) {
                throw ValidationException::withMessages([
                    'medico' => 'El profesional seleccionado no atiende tu obra social.',
                ]);
            }
            $pacienteId = $user->paciente->id;
        } else {
            // La asignacion segura por DNI queda para la siguiente etapa.
            $pacienteId = $request->input('paciente_id');
        }

        Turno::reservarSiDisponible($medico->id, $especialidad, $inicio, $fin, $pacienteId);

        if ($user->rol === 'medico') {
            return redirect()->route('usuarios.medico.panel')
                ->with('success', 'Turno reservado correctamente.');
        }

        return redirect()->route('usuarios.paciente.panel')
            ->with('success', 'Turno reservado correctamente.');
    }

    /**
     * 4. SHOW: Muestra el detalle de un turno puntual (protegido con Policy).
     */
    public function show($id)
    {
        $turno = Turno::with(['paciente.user', 'medico.user', 'especialidad'])->findOrFail($id);
        Gate::authorize('view', $turno);

        return view('usuarios.turnos.show', compact('turno'));
    }

    /**
     * 5. EDIT: Muestra el formulario para cambiar estado del turno (solo médico asignado).
     */
    public function edit($id)
    {
        $turno = Turno::findOrFail($id);
        Gate::authorize('cambiarEstado', $turno);

        return view('usuarios.edit', compact('turno'));
    }

    /**
     * 6. UPDATE: Actualiza el estado del turno (ej: confirmado, atendido).
     */
    public function update(Request $request, $id)
    {
        $turno = Turno::findOrFail($id);
        Gate::authorize('cambiarEstado', $turno);

        $datos = $request->validate([
            'estado' => 'required|in:pendiente,cancelado,finalizado,ausente',
        ]);

        $turno->update($datos);

        return redirect()->route('usuarios.medico.panel')
            ->with('success', 'Estado del turno actualizado.');
    }

    /**
     * 7. DELETE: Cancela el turno aplicando la regla de días mínimos con Carbon.
     * Al pasar a 'cancelado', el horario vuelve a quedar libre automáticamente.
     */
    public function delete($id)
    {
        $turno = Turno::findOrFail($id);

        // Policy: solo el paciente dueño o el médico asignado pueden cancelar
        Gate::authorize('cancelar', $turno);

        if ($turno->estado === 'cancelado') {
            return back()->withErrors(['turno' => 'Este turno ya se encuentra cancelado.']);
        }

        // Regla de negocio con Carbon: diferencia entre hoy y la fecha del turno
        $hoy = Carbon::now()->startOfDay();
        $fechaTurno = Carbon::parse($turno->fecha)->startOfDay();
        $diasRestantes = $hoy->diffInDays($fechaTurno, false);

        if ($diasRestantes < self::DIAS_MINIMOS_CANCELACION) {
            return back()->withErrors([
                'turno' => 'No se puede cancelar el turno con menos de ' . self::DIAS_MINIMOS_CANCELACION . ' días de anticipación.'
            ]);
        }

        // Marcamos como cancelado (libera el cupo automáticamente en disponibilidadMedico)
        $turno->update(['estado' => 'cancelado']);

        return redirect()->route('usuarios.paciente.panel')
            ->with('success', 'Turno cancelado correctamente. El horario volvió a quedar disponible.');
    }

    /**
     * ENDPOINT AJAX: Devuelve los médicos de una especialidad,
     * filtrando automáticamente por la obra social del paciente logueado.
     */
    public function medicosPorEspecialidad($especialidadId)
    {
        $user = auth()->user();

        $query = Medico::with('user')
            ->whereHas('especialidades', function ($q) use ($especialidadId) {
                $q->where('especialidades.id', $especialidadId);
            });

        // Filtro automático por obra social del paciente
        //modificacion por esto de muchas obras sociales
        if ($user && $user->rol === 'paciente' && $user->paciente && $user->paciente->obra_social) {
            $obraSocialPaciente = $user->paciente->obra_social;

            $query->where(function ($q) use ($obraSocialPaciente) {
                $q->whereJsonContains('obras_sociales', $obraSocialPaciente)
                    ->orWhere('obra_social', $obraSocialPaciente);
            });
        }

        $medicos = $query->get()->map(function ($medico) {
            return [
                'id'       => $medico->id,
                'nombre'   => $medico->user->name,
                'apellido' => '', // El nombre completo ya está concatenado en user->name
                'imagen'   => $medico->urlImagen($medico->imagen),
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
