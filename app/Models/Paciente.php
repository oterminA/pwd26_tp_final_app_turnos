<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * funcion que hace una 'transaccion' para manejar el hecho de que tanto en user como en paciente manejamos datos de 'paciente', entonces al actualizar se tiene que cambiar en ambos lados y esto desde el control no se puede manejar
     */
    public function updateDatos($datos)
    {
        DB::transaction(function () use ($datos) {
           //se actualizan los datos en la tabla user
            $datosUser = [
                'name'  => trim($datos['nombre'] . ' ' . $datos['apellido']),//acá concatenamos nombre y apellido porque eso es lo que representa el 'name' en la tabla user
                'email' => $datos['email'],
            ];

            if (!empty($datos['password'])) {
                $datosUser['password'] = $datos['password'];
            }

            $this->user->update($datosUser);

            //se actualizan los datos en la tabla paciente
            $this->update([
                'dni'              => $datos['dni'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'telefono'         => $datos['telefono'],
                'obra_social'      => $datos['obra_social'],
            ]);
        });
    }
}
