<?php

namespace App\Services;

use App\Exceptions\CategoriaException;
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
            throw new CategoriaException(
                'No se puede eliminar el estado porque tiene vehículos asociados.'
            );
        }

        $estado->delete();
    }
}