<?php

namespace App\Services;

use App\Exceptions\VehiculoException;
use App\Models\Estado;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;

class VehiculoService
{
    /**
     * para obtener el ID de cualquier estado del catálogo por su nombre.
     */
    protected function getEstadoIdByName(string $nombre): int
    {
        $estado = Estado::where('nombre', $nombre)->first();

        if (!$estado) {
            throw new VehiculoException("El estado '{$nombre}' no existe en el catálogo de estados.");
        }

        return $estado->id;
    }

    /**
     * ESTADO 1: Disponible / ESTADO 5: Para Venta
     * Registro inicial de vehículo asignando 'Disponible' o 'Para Venta'.
     */
    public function crear(array $data): Vehiculo
    {
        // Regla 1: Límite de kilometraje al crear
        if ($data['kilometraje'] > 5000) {
            throw new VehiculoException('No se permite el registro inicial de vehículos con más de 5,000 km.');
        }

        $annoActual = (int) date('Y');

        // Evaluación de estado inicial: 'Para Venta' si cumple antigüedad o km, de lo contrario 'Disponible'
        if (($annoActual - $data['anno']) >= 10 || $data['kilometraje'] > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        } else {
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

    /**
     * Actualiza el vehículo controlando histórico de kilometraje y pase automático a 'Para Venta'.
     */
    public function actualizar(Vehiculo $vehiculo, array $data): Vehiculo
    {
        $annoActual = (int) date('Y');
        $anno = $data['anno'] ?? $vehiculo->anno;

        if (isset($data['kilometraje'])) {
            // Regla de histórico: No se puede reducir el odómetro
            if ($data['kilometraje'] < $vehiculo->kilometraje) {
                throw new VehiculoException("El kilometraje ({$data['kilometraje']} km) no puede ser menor al histórico registrado ({$vehiculo->kilometraje} km).");
            }
        }

        $kilometrajeFinal = $data['kilometraje'] ?? $vehiculo->kilometraje;

        // Regla de reevaluación a 'Para Venta'
        if (($annoActual - $anno) >= 10 || $kilometrajeFinal > 80000) {
            $data['estado_id'] = $this->getEstadoIdByName('Para Venta');
        }

        $vehiculo->update($data);

        return $vehiculo->fresh(['categoria', 'estado']);
    }

    /**
     * ESTADO 2: Alquilado
     * Asigna la unidad a un contrato de renta previa validación de disponibilidad.
     */
    public function iniciarRenta(Vehiculo $vehiculo): Vehiculo
    {
        $estadoDisponibleId = $this->getEstadoIdByName('Disponible');

        if ($vehiculo->estado_id !== $estadoDisponibleId) {
            throw new VehiculoException("El vehículo no se puede alquilar porque su estado actual es '{$vehiculo->estado->nombre}'. Solo se permiten vehículos 'Disponible'.");
        }

        $vehiculo->update([
            'estado_id' => $this->getEstadoIdByName('Alquilado')
        ]);

        return $vehiculo->fresh('estado');
    }

    /**
     * ESTADO 3: En Mantenimiento
     * Transfiere el vehículo al taller mecánico para revisión preventiva/correctiva.
     */
    public function enviarAMantenimiento(Vehiculo $vehiculo): Vehiculo
    {
        if ($vehiculo->estado_id === $this->getEstadoIdByName('Alquilado')) {
            throw new VehiculoException('No se puede enviar a mantenimiento un vehículo que se encuentra actualmente alquilado.');
        }

        $vehiculo->update([
            'estado_id' => $this->getEstadoIdByName('En Mantenimiento')
        ]);

        return $vehiculo->fresh('estado');
    }

    /**
     * Finaliza la revisión mecanica y retorna el auto a 'Disponible' (o 'Para Venta' si cumple la regla).
     */
    public function completarMantenimiento(Vehiculo $vehiculo): Vehiculo
    {
        $annoActual = (int) date('Y');

        $nuevoEstado = (($annoActual - $vehiculo->anno) >= 10 || $vehiculo->kilometraje > 80000)
            ? 'Para Venta'
            : 'Disponible';

        $vehiculo->update([
            'estado_id' => $this->getEstadoIdByName($nuevoEstado)
        ]);

        return $vehiculo->fresh('estado');
    }

    /**
     * ESTADO 4: Inactivo
     * Realiza una desactivación administrativa del vehículo.
     */
    public function desactivar(Vehiculo $vehiculo): Vehiculo
    {
        if ($vehiculo->estado_id === $this->getEstadoIdByName('Alquilado')) {
            throw new VehiculoException('No se puede desactivar un vehículo que posee un contrato de alquiler activo.');
        }

        $vehiculo->update([
            'estado_id' => $this->getEstadoIdByName('Inactivo')
        ]);

        return $vehiculo->fresh('estado');
    }

    /**
     * Controla la eliminación física de registros.
     */
    public function eliminar(Vehiculo $vehiculo): void
    {
        if ($vehiculo->estado_id === $this->getEstadoIdByName('Alquilado') || $vehiculo->rentas()->where('estado', 'activa')->exists()) {
            throw new VehiculoException('No se puede eliminar el vehículo porque tiene contratos de alquiler activos.');
        }

        $vehiculo->delete();
    }
}