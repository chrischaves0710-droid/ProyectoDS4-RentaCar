<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    /**
     * Determina si este registro de Cliente
     * pertenece al usuario autenticado.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        return strcasecmp(
            $user->email,
            $cliente->correo
        ) === 0;
    }
}