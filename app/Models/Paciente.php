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
     * funcion para mostrar de formadinamica las obras sociales al paciente
    */
    public function obrasSocialesAsignadas(): array
{
    return $this->obra_social ? [$this->obra_social] : [];
}

    /**
     * funcion que hace una 'transaccion' para manejar el hecho de que tanto en user como en paciente manejamos datos de 'paciente', entonces al crear el paciente se tiene que crear en ambos lados y esto desde el control no se puede manejar
     */
    public function createDatos($datos)
    {
        return DB::transaction(function () use ($datos) {
            $user = User::create([
                'name'     => trim($datos['nombre'] . ' ' . $datos['apellido']),
                'email'    => $datos['email'],
                'password' => $datos['password'],
                'rol'      => 'paciente',
            ]);
            $user->paciente()->create([
                'dni'              => $datos['dni'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'telefono'         => $datos['telefono'],
                'obra_social'      => $datos['obra_social'] ?? null,
            ]);
            if ($user->rol === 'paciente') {
                return $user;
            }
            return $user;
        });
    }

    /**
     * funcion que hace una 'transaccion' para manejar el hecho de que tanto en user como en paciente manejamos datos de 'paciente', entonces al actualizar se tiene que cambiar en ambos lados y esto desde el control no se puede manejar
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

            //se actualizan los datos en la tabla paciente
            $this->update([
                'dni'              => $datos['dni'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'telefono'         => $datos['telefono'],
                'obra_social'      => $datos['obra_social'],
            ]);
        });
    }


    /**
     * accessor para el nombre del paciente, para usar en 'cuenta' con los old ya que se guardan en el name y no en apellido y nombre
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
     * accessor para el apellido del paciente, para usar en 'cuenta' con los old ya que se guardan en el name y no en apellido y nombre
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
}
