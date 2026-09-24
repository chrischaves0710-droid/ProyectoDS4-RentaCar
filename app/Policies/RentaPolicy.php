<?php

namespace App\Policies;

use App\Models\Renta;
use App\Models\User;

class RentaPolicy
{
    /**
     * Determina si esta renta pertenece
     * al usuario autenticado.
     */
    public function view(User $user, Renta $renta): bool
    {
        return $renta->cliente !== null
            && strcasecmp(
                $user->email,
                $renta->cliente->correo
            ) === 0;
    }
}