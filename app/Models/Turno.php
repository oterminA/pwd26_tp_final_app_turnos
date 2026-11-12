<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $fillable = [
        'paciente_id', 'medico_id', 'especialidad_id', 'fecha',
        'hora_inicio', 'hora_fin', 'estado', 'codigo'
    ];

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
