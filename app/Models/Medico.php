<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class Medico extends Model
{
    // esto quedó coordinado con los formularios y las migrations que habia
    protected $fillable = ['user_id', 'matricula', 'obra_social', 'imagen'];

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

    /**
     * funcion que hace una 'transaccion' para manejar el hecho de que tanto en user como en medico manejamos datos de 'medico', entonces al actualizar se tiene que cambiar en ambos lados y esto desde el control no se puede manejar
     */
    public function updateDatos($datos)
    {
        DB::transaction(function () use ($datos) {
            //se actualizan los datos en la tabla user
            $datosUser = [
                'name'  => $datos['name'],
                'email' => $datos['email'],
            ];

            if (!empty($datos['password'])) {
                $datosUser['password'] = Hash::make($datos['password']);
            }

            $this->user->update($datosUser);

            //se actualizan los datos en la tabla medico
            $this->update([
                'matricula'              => $datos['matricula'],
                'obra_social'      => $datos['obra_social'],
                'imagen'         => $datos['imagen'],
            ]);
        });
    }

    /**
     *
    */
    public function urlImagen(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }
        return Storage::disk('public')->url($ruta);
    }
}
