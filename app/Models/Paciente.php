<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = ['user_id', 'dni', 'telefono', 'fecha_nacimiento'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
