<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $fillable = [
        // esto quedó coordinado con los formularios y las migrations que habia
        'user_id',
        'dni',
        'telefono',
        'fecha_nacimiento',
        'obra_social',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
