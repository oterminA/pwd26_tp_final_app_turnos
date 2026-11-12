<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
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
