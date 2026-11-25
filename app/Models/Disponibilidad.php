<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';

    protected $fillable = ['medico_id', 'especialidad_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    /**
     * esta funcion es para mostrar los dias de disponibilidad en forma de strings
    */
    protected function diaNombre(): Attribute
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        return Attribute::make(
            get: fn() => $dias[$this->dia_semana] ?? (string) $this->dia_semana,
        );
    }

    protected $casts = [ //para sacar el carbon de las vistas y mantener las responsabilidades mvc
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
    ];

    // La superposicion se comprueba por medico y dia, sin filtrar especialidad.
    public static function haySuperposicion($medicoId, $dia, $inicio, $fin, $exceptoId = null): bool
    {
        $franjas = self::where('medico_id', $medicoId)
            ->where('dia_semana', $dia)
            ->when($exceptoId, fn ($query) => $query->where('id', '!=', $exceptoId))
            ->get();

        $inicioNuevo = Carbon::parse($inicio)->format('H:i:s');
        $finNuevo = Carbon::parse($fin)->format('H:i:s');

        foreach ($franjas as $franja) {
            $desde = Carbon::parse($franja->hora_inicio)->format('H:i:s');
            $hasta = Carbon::parse($franja->hora_fin)->format('H:i:s');
            if ($desde < $finNuevo && $hasta > $inicioNuevo) {
                return true;
            }
        }
        return false;
    }

    // Se aceptan las disponibilidades anteriores sin especialidad asignada.
    public static function cubreTurno($medicoId, $especialidadId, Carbon $inicio, Carbon $fin): bool
    {
        $franjas = self::where('medico_id', $medicoId)
            ->where('dia_semana', $inicio->dayOfWeekIso)
            ->where(function ($query) use ($especialidadId) {
                $query->where('especialidad_id', $especialidadId)
                    ->orWhereNull('especialidad_id');
            })->get();

        $horaInicio = $inicio->format('H:i:s');
        $horaFin = $fin->format('H:i:s');
        return $franjas->contains(function ($franja) use ($horaInicio, $horaFin) {
            $desde = Carbon::parse($franja->hora_inicio)->format('H:i:s');
            $hasta = Carbon::parse($franja->hora_fin)->format('H:i:s');
            return $desde <= $horaInicio && $hasta >= $horaFin;
        });
    }

    // Comprueba que los turnos pendientes sigan cubiertos por alguna franja.
    public function afectaTurnosPendientes(?array $datos = null): bool
    {
        $ahora = Carbon::now();
        $turnos = Turno::where('medico_id', $this->medico_id)
            ->where('estado', 'pendiente')
            ->whereDate('fecha', '>=', $ahora->toDateString())
            ->get();

        $otrasFranjas = self::where('medico_id', $this->medico_id)
            ->where('id', '!=', $this->id)->get();

        foreach ($turnos as $turno) {
            $fecha = $turno->fecha->toDateString();
            $inicio = Carbon::parse($fecha . ' ' . Carbon::parse($turno->hora_inicio)->format('H:i:s'));
            $fin = Carbon::parse($fecha . ' ' . Carbon::parse($turno->hora_fin)->format('H:i:s'));

            if ($fin->lte($ahora) || !$this->contieneTurno($this, $turno, $inicio, $fin)) {
                continue;
            }

            if ($datos !== null && $this->contieneTurno((object) $datos, $turno, $inicio, $fin)) {
                continue;
            }

            if (!$otrasFranjas->contains(fn ($franja) => $this->contieneTurno($franja, $turno, $inicio, $fin))) {
                return true;
            }
        }

        return false;
    }

    private function contieneTurno($franja, Turno $turno, Carbon $inicio, Carbon $fin): bool
    {
        return (int) $franja->dia_semana === $inicio->dayOfWeekIso
            && ($franja->especialidad_id === null || (int) $franja->especialidad_id === (int) $turno->especialidad_id)
            && Carbon::parse($franja->hora_inicio)->format('H:i:s') <= $inicio->format('H:i:s')
            && Carbon::parse($franja->hora_fin)->format('H:i:s') >= $fin->format('H:i:s');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
