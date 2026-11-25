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

    public static function proximosPorMedico($medicoId, Carbon $hoy, Carbon $limite)
    {
        return self::where('medico_id', $medicoId)
            ->whereBetween('fecha', [$hoy->toDateString(), $limite->toDateString()])
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

    public static function horariosDisponibles(Medico $medico, Especialidad $especialidad): array
    {
        $duracion = (int) $especialidad->duracion_turno;
        $hoy = Carbon::now();
        $limite = $hoy->copy()->addDays(30);
        $turnosPorFecha = self::proximosPorMedico($medico->id, $hoy, $limite);
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

                    if ($cursor->gt($hoy) && !$ocupado) {
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

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}
