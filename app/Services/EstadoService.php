<?php

namespace App\Services;

use App\Exceptions\EstadoException;
use App\Models\Estado;
use App\Models\Vehiculo;

class EstadoService
{
    public function crear(array $data)
    {
        return Estado::create($data);
    }

    public function actualizar(Estado $estado, array $data)
    {
        $estado->update($data);

        return $estado;
    }

    public function eliminar(Estado $estado)
    {
        $tieneVehiculos = Vehiculo::where('estado_id', $estado->id)->exists();

        // Regla de negocio: No se puede eliminar un estado si tiene vehículos asociados.
        if ($tieneVehiculos) {
            throw new EstadoException(
                'No se puede eliminar el estado porque tiene vehículos asociados.'
            );
        }

        $estado->delete();
    }
}