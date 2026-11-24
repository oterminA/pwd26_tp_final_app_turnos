<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';

    protected $fillable = ['medico_id', 'dia_semana', 'hora_inicio', 'hora_fin'];

    protected $casts = [ //para sacar el carbon de las vistas y mantener las responsabilidades mvc
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
