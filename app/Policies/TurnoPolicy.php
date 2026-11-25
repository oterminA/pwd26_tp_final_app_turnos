<?php

namespace App\Policies;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TurnoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * solo el paciente DUEÑO y el medico ASIGNADO pueden ver los detalles del turno
     */
    public function view(User $user, Turno $turno): bool
    {
        $esPacienteDuenio = $user->rol === 'paciente'
            && $user->paciente
            && $user->paciente->id === $turno->paciente_id;

        $esMedicoAsignado = $user->rol === 'medico'
            && $user->medico
            && $user->medico->id === $turno->medico_id;

        return $esPacienteDuenio || $esMedicoAsignado;
    }

    /**
     * solo el paciente DUEÑO y el meddico ASIGNADO pueden cancelar el turno
     */
    public function cancelar(User $user, Turno $turno): bool
    {
        return $this->view($user, $turno);
    }

    /**
     * solo el medico ASIGNADO puede cambiar el estado del turno
     */
    public function cambiarEstado(User $user, Turno $turno): bool
    {
        return $user->rol === 'medico'
            && $user->medico
            && $user->medico->id === $turno->medico_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Turno $turno): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Turno $turno): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Turno $turno): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Turno $turno): bool
    {
        return false;
    }

    /**
     * solo el paciente DUEÑO del turno o el medico ASIGNADO puede descargar el comprobante
     */
  public function descargarComprobante(User $user, Turno $turno): bool
{
    return $this->view($user, $turno);
}
}
