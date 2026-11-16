<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    // nombre de la tabla en la base de datos
    protected $table = 'especialidades';

    protected $fillable = ['nombre', 'descripcion', 'duracion_turno'];

    public function medicos()
    {
        return $this->belongsToMany(Medico::class, 'especialidad_medico');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
