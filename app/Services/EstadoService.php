<?php

namespace App\Services;

use App\Exceptions\EstadoException;
use App\Models\Estado;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class EstadoService
{
    private function verificarPermisosAdministrativos(): void
    {
        $user = Auth::user();

        if (!$user instanceof User || !$user->hasAnyRole([
            'Admin_General',
            'Admin_Inventarios',
        ])) {
            throw new AuthorizationException(
                'No autorizado: se requieren privilegios de administración de inventario.'
            );
        }
    }

    public function crear(array $data)
    {
        $this->verificarPermisosAdministrativos();

        return Estado::create($data);
    }

    public function actualizar(Estado $estado, array $data)
    {
        $this->verificarPermisosAdministrativos();

        $estado->update($data);

        return $estado;
    }

    public function eliminar(Estado $estado)
    {
        $this->verificarPermisosAdministrativos();

        $tieneVehiculos = Vehiculo::where('estado_id', $estado->id)->exists();

        if ($tieneVehiculos) {
            throw new EstadoException(
                'No se puede eliminar el estado porque tiene vehículos asociados.'
            );
        }

        $estado->delete();
    }
}