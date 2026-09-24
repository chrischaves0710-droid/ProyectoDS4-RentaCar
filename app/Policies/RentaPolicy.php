<?php

namespace App\Policies;

use App\Models\Renta;
use App\Models\User;

class RentaPolicy
{
    /**
     * Admin_General y Gestor_Rentas pueden listar todas.
     * Cliente puede acceder al listado, PERO luego debemos
     * filtrar el listado para que reciba únicamente sus rentas.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
            'Cliente',
        ]);
    }

    /**
     * Admin_General y Gestor_Rentas pueden consultar cualquier renta.
     * Cliente solamente puede consultar rentas asociadas a su Cliente.
     */
    public function view(User $user, Renta $renta): bool
    {
        if ($user->hasAnyRole(['Admin_General', 'Gestor_Rentas'])) {
            return true;
        }

        return $this->esPropietario($user, $renta);
    }

    /**
     * Admin_General y Gestor_Rentas pueden registrar rentas.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ]);
    }

    /**
     * Admin_General y Gestor_Rentas pueden actualizar rentas.
     */
    public function update(User $user, Renta $renta): bool
    {
        return $user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ]);
    }

    /**
     * Admin_General y Gestor_Rentas pueden eliminar rentas.
     */
    public function delete(User $user, Renta $renta): bool
    {
        return $user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ]);
    }

    public function restore(User $user, Renta $renta): bool
    {
        return false;
    }

    public function forceDelete(User $user, Renta $renta): bool
    {
        return false;
    }

    /**
     * Una renta pertenece al usuario Cliente cuando el correo
     * del Cliente asociado coincide con el email autenticado.
     */
    private function esPropietario(User $user, Renta $renta): bool
    {
        return $user->hasRole('Cliente')
            && $renta->cliente !== null
            && strcasecmp($user->email, $renta->cliente->correo) === 0;
    }
}