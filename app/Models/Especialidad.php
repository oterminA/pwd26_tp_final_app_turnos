<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidad extends Model
{
    use SoftDeletes;

    // nombre de la tabla
    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_turno'
    ];

    // relacion con los medicos
    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'especialidad_medico');
    }

    // relacion con los turnos
    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
    public function puedeDesactivarse(): bool
    {
        return !$this->turnos()->pendientesVigentes()->exists();
    }

    public function puedeCambiarDuracion(int $duracion): bool
    {
        return $duracion === (int) $this->duracion_turno || $this->puedeDesactivarse();
    }

    public function actualizarDatos(array $datos): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($datos) {
            if (!$this->puedeCambiarDuracion((int) $datos['duracion_turno'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['duracion_turno' => 'No se puede cambiar la duracion con turnos pendientes.']);
            }
            if ($datos['estado'] === 'inactiva' && !$this->trashed() && !$this->puedeDesactivarse()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['estado' => 'No se puede desactivar la especialidad porque tiene turnos pendientes.']);
            }
            $this->update(['nombre' => $datos['nombre'], 'descripcion' => $datos['descripcion'] ?? null, 'duracion_turno' => $datos['duracion_turno']]);
            if ($datos['estado'] === 'inactiva' && !$this->trashed()) {
                $this->delete();
            } elseif ($datos['estado'] === 'activa' && $this->trashed()) {
                $this->restore();
            }
        });
    }
}
