<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    // resumen de turnos del mes seleccionado
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'mes' => ['nullable', 'date_format:Y-m'],
            'medico' => ['nullable', 'integer', 'exists:medicos,id'],
        ]);

        $mes = $filtros['mes'] ?? now()->format('Y-m');
        $inicio = Carbon::createFromFormat('!Y-m-d', $mes . '-01')->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();
        $medicoId = $filtros['medico'] ?? null;

        $base = Turno::query()->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]);
        if ($medicoId) {
            $base->where('medico_id', $medicoId);
        }

        $total = (clone $base)->count();
        $finalizados = (clone $base)->where('estado', 'finalizado')->count();
        $cancelados = (clone $base)->where('estado', 'cancelado')->count();
        $pendientes = (clone $base)->where('estado', 'pendiente')->count();
        $ausentes = (clone $base)->where('estado', 'ausente')->count();
        $pacientesUnicos = (clone $base)->distinct()->count('paciente_id');
        $tasaCancelacion = $total ? round($cancelados / $total * 100, 1) : 0;

        $porDia = (clone $base)
            ->select('fecha', DB::raw('COUNT(*) AS total'))
            ->groupBy('fecha')->orderBy('fecha')->pluck('total', 'fecha');
        $dias = [];
        $cantidadDia = [];
        for ($dia = $inicio->copy(); $dia->lte($fin); $dia->addDay()) {
            $dias[] = $dia->format('d');
            $cantidadDia[] = (int) ($porDia[$dia->toDateString()] ?? 0);
        }

        $porEspecialidad = (clone $base)
            ->join('especialidades', 'turnos.especialidad_id', '=', 'especialidades.id')
            ->select('especialidades.nombre', DB::raw('COUNT(*) AS total'))
            ->groupBy('especialidades.id', 'especialidades.nombre')
            ->orderByDesc('total')->limit(6)->get();

        $porMedico = (clone $base)
            ->join('medicos', 'turnos.medico_id', '=', 'medicos.id')
            ->join('users', 'medicos.user_id', '=', 'users.id')
            ->select('medicos.id', 'users.name', DB::raw('COUNT(*) AS total'))
            ->groupBy('medicos.id', 'users.name')
            ->orderByDesc('total')->limit(6)->get();

        $proximos = Turno::with(['medico.user', 'especialidad'])
            ->whereDate('fecha', '>=', today())
            ->whereNotIn('estado', ['cancelado', 'finalizado', 'ausente'])
            ->when($medicoId, fn ($query) => $query->where('medico_id', $medicoId))
            ->orderBy('fecha')->orderBy('hora_inicio')->limit(8)->get();

        $medicos = Medico::withTrashed()->with('user')->get()
            ->sortBy(fn ($medico) => $medico->user->name ?? '')->values();

        return view('usuarios.admin.dashboard', [
            'mes' => $mes,
            'medicoId' => $medicoId,
            'medicos' => $medicos,
            'total' => $total,
            'finalizados' => $finalizados,
            'cancelados' => $cancelados,
            'pendientes' => $pendientes,
            'estadosGrafico' => [$pendientes, $finalizados, $cancelados, $ausentes],
            'ausentes' => $ausentes,
            'pacientesUnicos' => $pacientesUnicos,
            'tasaCancelacion' => $tasaCancelacion,
            'medicosActivos' => Medico::count(),
            'especialidadesActivas' => Especialidad::count(),
            'pacientesRegistrados' => Paciente::count(),
            'dias' => $dias,
            'cantidadDia' => $cantidadDia,
            'porEspecialidad' => $porEspecialidad,
            'porMedico' => $porMedico,
            'proximos' => $proximos,
        ]);
    }

    // devuelve los turnos del rango solicitado por el calendario (AJAX)
    public function calendario(Request $request)
    {
        $datos = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'medico' => ['nullable', 'integer', 'exists:medicos,id'],
        ]);

        $inicio = Carbon::parse($datos['start']);
        $fin = Carbon::parse($datos['end']);
        if ($inicio->diffInDays($fin) > 100) {
            return response()->json(['message' => 'El rango consultado es demasiado grande.'], 422);
        }

        $turnos = Turno::with(['medico.user', 'especialidad'])
            ->where('fecha', '>=', $inicio->toDateString())
            ->where('fecha', '<', $fin->toDateString())
            ->when($datos['medico'] ?? null, fn ($q, $id) => $q->where('medico_id', $id))
            ->orderBy('fecha')->orderBy('hora_inicio')->limit(1000)->get();

        return response()->json($turnos->map(function ($turno) {
            $medico = $turno->medico?->user?->name ?? 'Medico';
            $especialidad = $turno->especialidad?->nombre ?? 'Sin especialidad';
            $hora = substr((string) $turno->getRawOriginal('hora_inicio'), 0, 5);
            return [
                'id' => $turno->id,
                'title' => $hora . ' · ' . $medico,
                'start' => $turno->fecha->format('Y-m-d') . 'T' . substr((string) $turno->getRawOriginal('hora_inicio'), 0, 8),
                'allDay' => false,
                'color' => match ($turno->estado) {
                    'cancelado' => '#9aa6b2',
                    'finalizado' => '#267a62',
                    'ausente' => '#c48e47',
                    default => '#55b4a8',
                },
                'extendedProps' => [
                    'medico' => $medico,
                    'especialidad' => $especialidad,
                    'estado' => $turno->estado,
                    'hora' => $hora,
                ],
            ];
        }));
    }
}
