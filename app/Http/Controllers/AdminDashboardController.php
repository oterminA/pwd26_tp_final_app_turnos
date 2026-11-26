<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('usuarios.admin.dashboard', Turno::resumenMensual($mes, $inicio, $fin, $medicoId));
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
            ->whereDate('fecha', '>=', $inicio->toDateString())
            ->whereDate('fecha', '<', $fin->toDateString())
            ->when($datos['medico'] ?? null, fn ($q, $id) => $q->where('medico_id', $id))
            ->orderBy('fecha')->orderBy('hora_inicio')->limit(1000)->get();

        return response()->json($turnos->map(function ($turno) {
            $medico = $turno->medico?->user?->nombre_visible ?? 'Medico';
            $especialidad = $turno->especialidad?->nombre ?? 'Sin especialidad';
            $horaCompleta = substr((string) $turno->getRawOriginal('hora_inicio'), -8);
            $hora = substr($horaCompleta, 0, 5);
            return [
                'id' => $turno->id,
                'title' => $hora . ' · ' . $medico,
                'start' => $turno->fecha->format('Y-m-d') . 'T' . $horaCompleta,
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
