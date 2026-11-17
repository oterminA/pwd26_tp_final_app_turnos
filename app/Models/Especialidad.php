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
}
