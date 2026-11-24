<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';

    protected $fillable = ['medico_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

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

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
