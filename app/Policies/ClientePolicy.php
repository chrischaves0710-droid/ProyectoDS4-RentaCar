<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    /**
     * Admin_General y Gestor_Rentas pueden listar clientes.
     * El Cliente final no puede listar todos los clientes.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Admin_General',
            'Gestor_Rentas',
        ]);
    }

    /**
     * Admin_General y Gestor_Rentas pueden consultar cualquier cliente.
     * Cliente únicamente puede consultar su propio registro.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        if ($user->hasAnyRole(['Admin_General', 'Gestor_Rentas'])) {
            return true;
        }

        return $this->esPropietario($user, $cliente);
    }

    /**
     * Solamente Admin_General puede crear clientes.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin_General');
    }

    /**
     * Admin_General puede modificar cualquier cliente.
     * Cliente solamente puede modificar su propio registro.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        if ($user->hasRole('Admin_General')) {
            return true;
        }

        return $this->esPropietario($user, $cliente);
    }

    /**
     * Solamente Admin_General puede eliminar clientes.
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('Admin_General');
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return false;
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return false;
    }

    /**
     * Como actualmente no existe FK entre users y clientes,
     * relacionamos ambos registros mediante email/correo.
     */
    private function esPropietario(User $user, Cliente $cliente): bool
    {
        return $user->hasRole('Cliente')
            && strcasecmp($user->email, $cliente->correo) === 0;
    }
}