<?php

namespace App\Policies;

use App\Models\Disponibilidad;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DisponibilidadPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Disponibilidad $disponibilidad): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * solo el medico DUEÑO de la disponibilidad puede modificarla
     */
    public function update(User $user, Disponibilidad $disponibilidad): bool
    {
        return $user->rol === 'medico'
            && $user->medico
            && $user->medico->id === $disponibilidad->medico_id;
    }

    /**
     * solo el medico DUEÑO de la disponibilidad puede eliminarla
     */
    public function delete(User $user, Disponibilidad $disponibilidad): bool
    {
        return $this->update($user, $disponibilidad);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Disponibilidad $disponibilidad): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Disponibilidad $disponibilidad): bool
    {
        return false;
    }
}

