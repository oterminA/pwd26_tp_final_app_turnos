<?php

namespace App\Policies;

use App\Models\Medico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicoPolicy
{
    /**
     * solo el admin puede ver el listado general de medicos
     */
    public function viewAny(User $user): bool
    {
        return $user->rol === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Medico $medico): bool
    {
        return $user->rol === 'medico' && $user->id === $medico->user_id;
    }

    /**
     * solo el admin puede crear al medico
     */
    public function create(User $user): bool
    {
        return $user->rol === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Medico $medico): bool
    {
        return $user->rol === 'medico' && $user->id === $medico->user_id;
    }

    /**
     * solo el admin puede borrar al medico
     */
    public function delete(User $user, Medico $medico): bool
    {
        return $user->rol === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Medico $medico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Medico $medico): bool
    {
        return false;
    }
}
