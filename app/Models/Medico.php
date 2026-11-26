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


    public static function crearDesdeAdmin(array $datos, ?string $rutaImagen): self
    {
        return DB::transaction(function () use ($datos, $rutaImagen) {
            $user = User::create([
                'name' => trim($datos['nombre']) . ' | ' . trim($datos['apellido']),
                'email' => $datos['email'],
                'password' => $datos['password'], // User lo hashea por casts()
                'rol' => 'medico',
            ]);

            $medico = Medico::create([
                'user_id' => $user->id,
                'tipoMat' => $datos['tipoMat'],
                'numMat' => $datos['numMat'],
                'matricula' => $datos['tipoMat'] . '-' . $datos['numMat'],
                'obra_social' => $datos['obras_sociales'][0], // valor anterior para compatibilidad
                'obras_sociales' => $datos['obras_sociales'],
                'imagen' => $rutaImagen,
            ]);
            $medico->especialidades()->sync($datos['especialidades']);
            return $medico;
        });
    }

    public function actualizarDesdeAdmin(array $datos, ?string $rutaImagen): void
    {
        DB::transaction(function () use ($datos, $rutaImagen) {
            if ($datos['estado'] === 'inactivo' && !$this->trashed() && $this->tieneTurnosFuturos()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['estado' => 'No se puede desactivar un medico con turnos futuros pendientes.']);
            }
            $userDatos = [
                'name' => trim($datos['nombre']) . ' | ' . trim($datos['apellido']),
                'email' => $datos['email'],
            ];
            if (!empty($datos['password'])) {
                $userDatos['password'] = $datos['password'];
            }
            $this->user->update($userDatos);

            $this->update([
                'tipoMat' => $datos['tipoMat'],
                'numMat' => $datos['numMat'],
                'matricula' => $datos['tipoMat'] . '-' . $datos['numMat'],
                'obra_social' => $datos['obras_sociales'][0], // valor anterior para compatibilidad
                'obras_sociales' => $datos['obras_sociales'],
                'imagen' => $rutaImagen ?? $this->imagen,
            ]);
            $this->actualizarEspecialidades($datos['especialidades']);

            if ($datos['estado'] === 'inactivo' && !$this->trashed()) {
                $this->delete();
            } elseif ($datos['estado'] === 'activo' && $this->trashed()) {
                $this->restore();
            }
        });
    }

    public function tieneTurnosFuturos(): bool
    {
        return $this->turnos()->pendientesVigentes()->exists();
    }

    public function validarEspecialidades(array $ids): void
    {
        // Solo se quitan especialidades activas que el formulario permite editar.
        $quitadas = $this->especialidades()->whereNotIn('especialidades.id', $ids)->get();
        foreach ($quitadas as $especialidad) {
            if ($this->disponibilidades()->where(function ($q) use ($especialidad) {
                $q->where('especialidad_id', $especialidad->id)->orWhereNull('especialidad_id');
            })->exists() || $this->turnos()->pendientesVigentes()->where('especialidad_id', $especialidad->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'especialidades' => 'No se puede quitar ' . $especialidad->nombre . ' porque tiene disponibilidades o turnos pendientes.',
                ]);
            }
        }
    }

    private function actualizarEspecialidades(array $ids): void
    {
        $this->validarEspecialidades($ids);
        // Conservamos las asociaciones inactivas que no aparecen en el formulario.
        $inactivas = $this->especialidades()->onlyTrashed()->pluck('especialidades.id')->all();
        $this->especialidades()->sync(array_merge($ids, $inactivas));
    }

    public function updateDatos($datos)
    {
        DB::transaction(function () use ($datos) {
            $datosUser = [
                'name' => trim($datos['nombre']) . ' | ' . trim($datos['apellido']),
                'email' => $datos['email'],
            ];
            if (!empty($datos['password'])) {
                $datosUser['password'] = $datos['password'];
            }
            $this->user->update($datosUser);
            $datosMedico = [];
            if (isset($datos['obras_sociales'])) {
                $datosMedico['obras_sociales'] = $datos['obras_sociales'];
                $datosMedico['obra_social'] = $datos['obras_sociales'][0];
            }
            if (!empty($datos['imagen'])) {
                $datosMedico['imagen'] = $datos['imagen'];
            }
            $this->update($datosMedico);
            if (isset($datos['especialidades'])) {
                $this->actualizarEspecialidades($datos['especialidades']);
            }
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
        return $this->user?->nombre ?? '';
    }

    /**
     * accessor para el apellido del medico, para usar en 'cuenta' con los old ya que se guardan en el name y no en apellido y nombre
     */
    public function getApellidoAttribute(): string
    {
        return $this->user?->apellido ?? '';
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
