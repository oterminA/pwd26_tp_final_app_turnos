<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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
        $request->validate([
            'medico'       => 'required|exists:medicos,id',
            'especialidad' => 'required|exists:especialidades,id',
            'fecha_hora'   => 'required|date_format:Y-m-d H:i:s',
        ]);

        $user = auth()->user();
        $medico = Medico::findOrFail($request->medico);

        // Si el usuario es paciente, verificamos que coincida la obra social
        //modificado con esto de muchas obras sociales
        if ($user->rol === 'paciente' && $user->paciente) {
            $coberturasMedico = is_array($medico->obras_sociales)
                ? $medico->obras_sociales
                : (json_decode($medico->obras_sociales ?? '[]', true) ?: []);

            $obraSocialPaciente = $user->paciente->obra_social;

            $atiendeObraSocial = ($obraSocialPaciente && in_array($obraSocialPaciente, $coberturasMedico, true))
                || (!empty($medico->obra_social) && $medico->obra_social === $obraSocialPaciente);

            if (!$atiendeObraSocial) {
                return back()->withErrors([
                    'medico' => 'El profesional seleccionado no atiende tu obra social.'
                ]);
            }

            $pacienteId = $user->paciente->id;
        } else {
            // Si un médico saca un turno, puede venir un paciente_id en el request
            $pacienteId = $request->input('paciente_id');
        }

        $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $request->fecha_hora);
        $duracion = $medico->duracion_turno ?? 30;
        $fin = $inicio->copy()->addMinutes($duracion);

        // Verificamos que ese horario no esté ocupado por otro turno activo (ignoramos los cancelados)
        $ocupado = Turno::where('medico_id', $medico->id)
            ->whereDate('fecha', $inicio->toDateString())
            ->whereTime('hora_inicio', $inicio->format('H:i:s'))
            ->where('estado', '!=', 'cancelado')
            ->exists();

        if ($ocupado) {
            return back()->withErrors([
                'fecha_hora' => 'Ese horario acaba de ser ocupado. Por favor elegí otro.'
            ]);
        }

        // Creamos el turno con un código único
        Turno::create([
            'paciente_id'     => $pacienteId,
            'medico_id'       => $medico->id,
            'especialidad_id' => $request->especialidad,
            'fecha'           => $inicio->toDateString(),
            'hora_inicio'     => $inicio->format('H:i:s'),
            'hora_fin'        => $fin->format('H:i:s'),
            'estado'          => 'pendiente',
            'codigo'          => strtoupper(Str::random(8)),
        ]);

        //cambiado para que pueda redirigir tanto al panel del medico como al del paciente segun de donde venga la peticion de turno
        if ($user && $user->isMedico()) {
            $respuesta = redirect()->route('usuarios.medico.panel')
                ->with('success', 'Turno reservado correctamente.');
        } else {
            $respuesta = redirect()->route('usuarios.paciente.panel')
                ->with('success', 'Turno reservado correctamente.');
        }

        return $respuesta;
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
    public function disponibilidadMedico($medicoId)
    {
        $medico = Medico::with('disponibilidades')->findOrFail($medicoId);
        $duracion = $medico->duracion_turno ?? 30;

        // Mapa de dayOfWeek de Carbon (0 = domingo ... 6 = sábado)
        $mapaDias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

        $hoy = Carbon::now();
        $limite = $hoy->copy()->addDays(30);

        // Traemos solo turnos NO cancelados del médico en los próximos 30 días
        $turnosOcupados = Turno::where('medico_id', $medico->id)
            ->whereBetween('fecha', [$hoy->toDateString(), $limite->toDateString()])
            ->where('estado', '!=', 'cancelado')
            ->get()
            ->groupBy(fn($t) => Carbon::parse($t->fecha)->toDateString())
            ->map(fn($grupo) => $grupo->map(fn($t) => Carbon::parse($t->hora_inicio)->format('H:i'))->toArray())
            ->toArray();

        $disponibilidadFinal = [];

        // Recorremos día por día los próximos 30 días
        for ($fecha = $hoy->copy(); $fecha->lte($limite); $fecha->addDay()) {
            $nombreDia = $mapaDias[$fecha->dayOfWeek];
            $fechaStr = $fecha->toDateString();

            // $franjas = $medico->disponibilidades->where('dia_semana', $nombreDia);
            $franjas = $medico->disponibilidades->where('dia_semana', $fecha->dayOfWeekIso);
            if ($franjas->isEmpty()) {
                continue;
            }

            $horariosLibres = [];
            $ocupadosHoy = $turnosOcupados[$fechaStr] ?? [];

            foreach ($franjas as $franja) {
                $horaInicioStr = Carbon::parse($franja->hora_inicio)->format('H:i:s');
                $horaFinStr = Carbon::parse($franja->hora_fin)->format('H:i:s');

                $cursor = Carbon::parse($fechaStr . ' ' . $horaInicioStr);
                $finFranja = Carbon::parse($fechaStr . ' ' . $horaFinStr);

                while ($cursor->copy()->addMinutes($duracion)->lte($finFranja)) {
                    $horaStr = $cursor->format('H:i');

                    // Evitamos horarios ya pasados si es el día de hoy, y horarios ocupados
                    $esFuturo = $cursor->gt($hoy);
                    if ($esFuturo && !in_array($horaStr, $ocupadosHoy)) {
                        $horariosLibres[] = $horaStr;
                    }

                    $cursor->addMinutes($duracion);
                }
            }

            if (!empty($horariosLibres)) {
                $disponibilidadFinal[$fechaStr] = array_values(array_unique($horariosLibres));
            }
        }

        return response()->json($disponibilidadFinal);
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
