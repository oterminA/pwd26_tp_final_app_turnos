<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    protected $fillable = ['user_id', 'matricula'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_medico');
    }

    public function disponibilidades()
    {
        return $this->hasMany(Disponibilidad::class);
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
