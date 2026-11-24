<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Medico extends Model
{
    use SoftDeletes;
    // esto quedó coordinado con los formularios y las migrations que habia
    protected $fillable = ['user_id', 'matricula', 'tipoMat', 'numMat', 'obra_social', 'obras_sociales', 'imagen', 'duracion_turno'];

    protected $casts = [
        'obras_sociales' => 'array',
    ];

    // permite leer las coberturas nuevas y tambien los datos anteriores
    public function obrasSocialesAsignadas(): array
    {
        if (!empty($this->obras_sociales)) {
            return $this->obras_sociales;
        }

        return $this->obra_social ? [$this->obra_social] : [];
    }

    // metodo disponible para que el modulo de turnos lo use cuando se integre
    public function aceptaObraSocial(?string $codigo): bool
    {
        return $codigo !== null && in_array($codigo, $this->obrasSocialesAsignadas(), true);
    }

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
                'name'  => trim($datos['nombre'] . ' ' . $datos['apellido']), //acá concatenamos nombre y apellido porque eso es lo que representa el 'name' en la tabla user
                'email' => $datos['email'],
            ];

            if (!empty($datos['password'])) {
                $datosUser['password'] = $datos['password'];
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
     *funcion para poder mostrar imagenes de medico
     */
    public function urlImagen(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        return asset($ruta);
    }



    ///scope
    /**
     * scope para mostrar los medicos en el landing en la parte de equipo
    */
    public function scopeEquipoPublico($query)
    {
        return $query->with([
            'user',
            'especialidades',
        ]);
    }
}
