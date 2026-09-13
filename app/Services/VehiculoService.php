<?php

namespace App\Services;

use App\Exceptions\VehiculoException;
use App\Models\Estado;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class VehiculoService
{
    protected function getEstadoIdByName(string $nombre): int
    {
        $estado = Estado::where('nombre', $nombre)->first();

        if (!$estado) {
            throw new VehiculoException("El estado '{$nombre}' no existe en el catálogo de estados.");
        }

        return $estado->id;
    }

    public function crear(array $data): Vehiculo
    {
        // Regla 1: No permitir registro inicial con más de 5,000 km
        if ($data['kilometraje'] > 5000) {
            throw new VehiculoException('No se permite el registro inicial de vehículos con más de 5,000 km.');
        }

        $annoActual = (int) date('Y');

        // Reglas 2 y 3: Asignación automática a 'Para Venta' si cumple antigüedad o kilometraje
        if (($annoActual - $data['anno']) >= 10 || $data['kilometraje'] > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        } elseif (!isset($data['estado_id'])) {
            $data['estado_id'] = $this->getEstadoIdByName('Disponible');
        }

        return DB::transaction(function () use ($data) {
            $vehiculo = Vehiculo::create($data);

            DB::table('auditoria_vehiculos')->insert([
                'vehiculo_id' => $vehiculo->id,
                'accion' => 'REGISTRO_INICIAL',
                'created_at' => now(),
            ]);

            return $vehiculo->load(['categoria', 'estado']);
        });
    }

    public function actualizar(Vehiculo $vehiculo, array $data): Vehiculo
    {
        $annoActual = (int) date('Y');
        $anno = $data['anno'] ?? $vehiculo->anno;

        if (isset($data['kilometraje'])) {
            // Regla 4: No permitir reducir el kilometraje histórico
            if ($data['kilometraje'] < $vehiculo->kilometraje) {
                throw new VehiculoException("El kilometraje ({$data['kilometraje']} km) no puede ser menor al histórico registrado ({$vehiculo->kilometraje} km).");
            }
        }

        $kilometrajeFinal = $data['kilometraje'] ?? $vehiculo->kilometraje;

        // Reglas 2 y 3 en actualización
        if (($annoActual - $anno) >= 10 || $kilometrajeFinal > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        }

        $vehiculo->update($data);

        return $vehiculo->fresh(['categoria', 'estado']);
    }

    public function eliminar(Vehiculo $vehiculo): void
    {
        // Regla 5: Bloquea si la fecha de fin del alquiler es posterior o igual a la fecha actual
        if ($vehiculo->rentas()->where('fecha_fin', '>=', now()->toDateString())->exists()) {
            throw new VehiculoException('No se puede eliminar el vehículo porque tiene contratos de alquiler activos.');
        }

        $vehiculo->delete();
    }
}