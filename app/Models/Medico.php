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
    'name'  => trim($datos['nombre']) . ' | ' . trim($datos['apellido']),
    'email' => $datos['email'],
];

            if (!empty($datos['password'])) {
                $datosUser['password'] = $datos['password'];
            }

            $this->user->update($datosUser);

            $datosMedico = [
                'matricula' => $datos['matricula'] ?? $this->matricula,
            ];

            if (array_key_exists('obra_social', $datos)) {
                $datosMedico['obra_social'] = $datos['obra_social'];
            } elseif (!empty($datos['obras_sociales']) && is_array($datos['obras_sociales'])) {
                $datosMedico['obra_social'] = $datos['obras_sociales'][0];
            }

            if (!empty($datos['imagen'])) {
                $datosMedico['imagen'] = $datos['imagen'];
            }

            $this->update($datosMedico);
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


    /**
     * accessor para el nombre del medico, para usar en 'cuenta' con los old ya que se guardan en el name y no en apellido y nombre
     */
    public function getNombreAttribute(): string
    {
        $name = trim($this->user?->name ?? '');
        if (str_contains($name, '|')) {
            return trim(explode('|', $name, 2)[0]);
        }

        $partes = preg_split('/\s+/', $name);
        return $partes[0] ?? '';
    }

    /**
     * accessor para el apellido del medico, para usar en 'cuenta' con los old ya que se guardan en el name y no en apellido y nombre
     */
    public function getApellidoAttribute(): string
    {
        $name = trim($this->user?->name ?? '');
        if (str_contains($name, '|')) {
            return trim(explode('|', $name, 2)[1] ?? '');
        }

        $partes = preg_split('/\s+/', $name);
        array_shift($partes);
        return implode(' ', $partes);
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
