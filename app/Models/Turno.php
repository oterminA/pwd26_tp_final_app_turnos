<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Turno extends Model
{
    protected $fillable = [
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'codigo'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime',
    ];

    public static function proximosPorMedico($medicoId, Carbon $hoy, Carbon $limite, $pacienteId = null)
    {
        return self::where(function ($query) use ($medicoId, $pacienteId) {
            $query->where('medico_id', $medicoId);
            if ($pacienteId !== null) {
                $query->orWhere('paciente_id', $pacienteId);
            }
        })
            ->whereDate('fecha', '>=', $hoy->toDateString())
            ->whereDate('fecha', '<=', $limite->toDateString())
            ->where('estado', '!=', 'cancelado')
            ->get()
            ->groupBy(fn ($turno) => $turno->fecha->toDateString());
    }

    public static function hayTurnoSuperpuesto($medicoId, Carbon $inicio, Carbon $fin): bool
    {
        // Usamos HH:mm:ss para comparar con las columnas TIME de la BD.
        return self::where('medico_id', $medicoId)
            ->whereDate('fecha', $inicio->toDateString())
            // El cast datetime puede guardar fecha + hora en una columna TIME.
            // Tomamos las ultimas 8 posiciones para comparar solo HH:mm:ss.
            ->whereRaw('SUBSTR(hora_inicio, -8) < ?', [$fin->format('H:i:s')])
            ->whereRaw('SUBSTR(hora_fin, -8) > ?', [$inicio->format('H:i:s')])
            ->where('estado', '!=', 'cancelado')
            ->exists();
    }

    // Conserva junta la comprobacion y la creacion del turno.
    public static function reservarSiDisponible($medicoId, Especialidad $especialidad, Carbon $inicio, Carbon $fin, $pacienteId): self
    {
        return DB::transaction(function () use ($medicoId, $especialidad, $inicio, $fin, $pacienteId) {
            Paciente::whereKey($pacienteId)->lockForUpdate()->firstOrFail();
            $medico = Medico::whereKey($medicoId)->lockForUpdate()->firstOrFail();

            if (!$medico->especialidades()->where('especialidades.id', $especialidad->id)->exists()) {
                throw ValidationException::withMessages([
                    'especialidad' => 'El profesional no atiende esta especialidad.',
                ]);
            }

            if (!Disponibilidad::cubreTurno($medico->id, $especialidad->id, $inicio, $fin)) {
                throw ValidationException::withMessages([
                    'fecha_hora' => 'El turno no esta dentro de la disponibilidad de esta especialidad.',
                ]);
            }

            if (self::hayTurnoSuperpuesto($medico->id, $inicio, $fin)) {
                throw ValidationException::withMessages([
                    'fecha_hora' => 'Ese horario ya esta ocupado. Elegi otro turno.',
                ]);
            }

            if (self::where('paciente_id', $pacienteId)
                ->whereDate('fecha', $inicio->toDateString())
                ->whereRaw('SUBSTR(hora_inicio, -8) < ?', [$fin->format('H:i:s')])
                ->whereRaw('SUBSTR(hora_fin, -8) > ?', [$inicio->format('H:i:s')])
                ->where('estado', '!=', 'cancelado')->exists()) {
                throw ValidationException::withMessages([
                    'fecha_hora' => 'Ya tenes otro turno en ese horario.',
                ]);
            }

            $horarios = self::horariosDisponibles($medico, $especialidad, $pacienteId);
            if ($inicio->format('s') !== '00'
                || !$fin->eq($inicio->copy()->addMinutes((int) $especialidad->duracion_turno))
                || !in_array($inicio->format('H:i'), $horarios[$inicio->toDateString()] ?? [], true)) {
                throw ValidationException::withMessages([
                    'fecha_hora' => 'Elegi uno de los horarios disponibles del calendario.',
                ]);
            }

            return self::create([
                'paciente_id' => $pacienteId,
                'medico_id' => $medico->id,
                'especialidad_id' => $especialidad->id,
                'fecha' => $inicio->toDateString(),
                'hora_inicio' => $inicio->format('H:i:s'),
                'hora_fin' => $fin->format('H:i:s'),
                'estado' => 'pendiente',
                'codigo' => strtoupper(Str::random(8)),
            ]);
        });
    }

    public static function horariosDisponibles(Medico $medico, Especialidad $especialidad, $pacienteId = null): array
    {
        $duracion = (int) $especialidad->duracion_turno;
        if ($duracion < 1 || $medico->trashed() || $especialidad->trashed()) {
            return [];
        }
        $hoy = Carbon::now();
        $limite = $hoy->copy()->addDays(30);
        $turnosPorFecha = self::proximosPorMedico($medico->id, $hoy, $limite, $pacienteId);
        $medico->loadMissing('disponibilidades');
        $resultado = [];

        for ($fecha = $hoy->copy()->startOfDay(); $fecha->lte($limite); $fecha->addDay()) {
            $dia = $fecha->toDateString();
            $franjas = $medico->disponibilidades
                ->where('dia_semana', $fecha->dayOfWeekIso)
                ->filter(fn ($franja) => $franja->especialidad_id === null || (int) $franja->especialidad_id === (int) $especialidad->id);
            $horarios = [];

            foreach ($franjas as $franja) {
                $cursor = Carbon::parse($dia . ' ' . Carbon::parse($franja->hora_inicio)->format('H:i:s'));
                $finFranja = Carbon::parse($dia . ' ' . Carbon::parse($franja->hora_fin)->format('H:i:s'));

                while ($cursor->copy()->addMinutes($duracion)->lte($finFranja)) {
                    $finCandidato = $cursor->copy()->addMinutes($duracion);
                    $ocupado = ($turnosPorFecha[$dia] ?? collect())->contains(function ($turno) use ($dia, $cursor, $finCandidato) {
                        $inicioOcupado = Carbon::parse($dia . ' ' . Carbon::parse($turno->hora_inicio)->format('H:i:s'));
                        $finOcupado = Carbon::parse($dia . ' ' . Carbon::parse($turno->hora_fin)->format('H:i:s'));
                        return $inicioOcupado->lt($finCandidato) && $finOcupado->gt($cursor);
                    });

                    if ($cursor->gt($hoy) && $cursor->format('s') === '00' && !$ocupado) {
                        $horarios[] = $cursor->format('H:i');
                    }
                    $cursor->addMinutes($duracion);
                }
            }

            if ($horarios) {
                $resultado[$dia] = array_values(array_unique($horarios));
                sort($resultado[$dia]);
            }
        }
        return $resultado;
    }

    public function puedeCambiarEstado(?string $estado = null): bool
    {
        if ($this->estado !== 'pendiente') {
            return false;
        }

        $fecha = $this->fecha->toDateString();
        $inicio = Carbon::parse($fecha . ' ' . Carbon::parse($this->hora_inicio)->format('H:i:s'));
        $fin = Carbon::parse($fecha . ' ' . Carbon::parse($this->hora_fin)->format('H:i:s'));
        $ahora = Carbon::now();

        if ($estado === 'ausente') {
            return $ahora->gte($fin);
        }
        if ($estado === null || $estado === 'finalizado') {
            return $ahora->gte($inicio);
        }
        return false;
    }

    public function puedeCancelar(): bool
    {
        return $this->estado === 'pendiente';
    }

    // Usamos fecha y hora exactas, no solo los dias del calendario.
    public function puedeCancelarPorPaciente(): bool
    {
        if (!$this->puedeCancelar()) {
            return false;
        }

        $fechaHoraTurno = Carbon::parse(
            $this->fecha->toDateString() . ' ' . Carbon::parse($this->hora_inicio)->format('H:i:s')
        );
        return Carbon::now()->addHours(48)->lt($fechaHoraTurno);
    }

    public function cancelar(): void
    {
        $this->update(['estado' => 'cancelado']);
    }

    public function marcarEstado(string $estado): void
    {
        $this->update(['estado' => $estado]);
    }

    // Turnos pendientes que todavia no terminaron.
    public function scopePendientesVigentes($query)
    {
        $ahora = now();
        return $query->where('estado', 'pendiente')->where(function ($q) use ($ahora) {
            $q->whereDate('fecha', '>', $ahora->toDateString())
                ->orWhere(function ($q) use ($ahora) {
                    $q->whereDate('fecha', $ahora->toDateString())
                        ->whereRaw('SUBSTR(hora_fin, -8) > ?', [$ahora->format('H:i:s')]);
                });
        });
    }

    public static function resumenMensual($mes, Carbon $inicio, Carbon $fin, $medicoId): array
    {
        $base = Turno::query()->whereDate('fecha', '>=', $inicio->toDateString())
            ->whereDate('fecha', '<=', $fin->toDateString());
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
            ->selectRaw('DATE(fecha) AS dia, COUNT(*) AS total')
            ->groupByRaw('DATE(fecha)')->orderBy('dia')->pluck('total', 'dia');
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
            ->select('medicos.id', DB::raw("REPLACE(users.name, ' | ', ' ') AS name"), DB::raw('COUNT(*) AS total'))
            ->groupBy('medicos.id', 'users.name')
            ->orderByDesc('total')->limit(6)->get();

        $proximos = Turno::with(['medico.user', 'especialidad'])
            ->whereDate('fecha', '>=', today())
            ->whereNotIn('estado', ['cancelado', 'finalizado', 'ausente'])
            ->when($medicoId, fn ($query) => $query->where('medico_id', $medicoId))
            ->orderBy('fecha')->orderBy('hora_inicio')->limit(8)->get();

        $medicos = Medico::withTrashed()->with('user')->get()
            ->sortBy(fn ($medico) => $medico->user->nombre_visible ?? '')->values();

        return [
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
        ];
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class)->withTrashed();
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class)->withTrashed();
    }
}
