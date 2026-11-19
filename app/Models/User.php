<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }

    public function medico()
    {
        return $this->hasOne(Medico::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', //con esto no hace falta hashear la contraseña en medico o paciente
        ];
    }

    /**
     * funcion que devuelve el rol que tiene ese usuario logueado, se usa para filtrar las vistas y ayude a que si se tiene que cambiar el rol se modifique desde acá
     */
    public function isPaciente()
    {
        return $this->rol === 'paciente';
    }

    /**
     * funcion que devuelve el rol que tiene ese usuario logueado, se usa para filtrar las vistas y ayude a que si se tiene que cambiar el rol se modifique desde acá
     */
    public function isMedico()
    {
        return $this->rol === 'medico';
    }

    /**
     * funcion que devuelve el rol que tiene ese usuario logueado, se usa para filtrar las vistas y ayude a que si se tiene que cambiar el rol se modifique desde acá
     */
    public function isAdmin()
    {
        return $this->rol === 'admin';
    }

}
