<?php

namespace App\Services;

use App\Exceptions\RentaException;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentaService
{
    public function listar(array $filtros)
    {
        $query = Renta::with(['cliente', 'vehiculo', 'accesorios']);

        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        if (!empty($filtros['vehiculo_id'])) {
            $query->where('vehiculo_id', $filtros['vehiculo_id']);
        }

        return $query->paginate(10);
    }

    public function obtener($id): Renta
    {
        $renta = Renta::with([
            'cliente',
            'vehiculo',
            'accesorios'
        ])->find($id);

        if (!$renta) {
            throw new RentaException('Renta no encontrada.');
        }

        return $renta;
    }

    public function crear(array $datos): Renta
    {
        $vehiculo = Vehiculo::with('estado')
            ->findOrFail($datos['vehiculo_id']);

        $this->validarDisponibilidad($vehiculo);

        $inicio = Carbon::parse($datos['fecha_inicio']);
        $fin = Carbon::parse($datos['fecha_fin']);

        $dias = $inicio->diffInDays($fin);

        if ($dias < 1) {
            throw new RentaException(
                'La renta debe tener una duración mínima de un día.'
            );
        }

        $datos['monto_total'] = $dias * $datos['precio_diario'];

        return DB::transaction(function () use ($datos, $vehiculo) {

            $renta = Renta::create($datos);

            $estadoAlquilado = Estado::where(
                'nombre',
                'Alquilado'
            )->first();

            if (!$estadoAlquilado) {
                throw new RentaException(
                    'No se encontró el estado Alquilado.'
                );
            }

            $vehiculo->update([
                'estado_id' => $estadoAlquilado->id
            ]);

            return $renta->load([
                'cliente',
                'vehiculo',
                'accesorios'
            ]);
        });
    }

    public function actualizar($id, array $datos): Renta
    {
        $renta = $this->obtener($id);

        $fechaInicio = Carbon::parse(
            $datos['fecha_inicio'] ?? $renta->fecha_inicio
        );

        $fechaFin = Carbon::parse(
            $datos['fecha_fin'] ?? $renta->fecha_fin
        );

        if ($fechaFin->lte($fechaInicio)) {
            throw new RentaException(
                'La fecha final debe ser posterior a la fecha inicial.'
            );
        }

        $precio = $datos['precio_diario'] ?? $renta->precio_diario;
        $dias = $fechaInicio->diffInDays($fechaFin);

        $datos['monto_total'] = $dias * $precio;

        $renta->update($datos);

        return $renta->load([
            'cliente',
            'vehiculo',
            'accesorios'
        ]);
    }

    public function eliminar($id): void
    {
        $renta = $this->obtener($id);

        $renta->delete();
    }

    private function validarDisponibilidad(Vehiculo $vehiculo): void
    {
        if (!$vehiculo->estado) {
            throw new RentaException(
                "El vehículo {$vehiculo->placa} no tiene estado asignado."
            );
        }

        if ($vehiculo->estado->nombre !== 'Disponible') {
            throw new RentaException(
                "El vehículo {$vehiculo->placa} no está disponible " .
                "(Estado actual: {$vehiculo->estado->nombre})."
            );
        }
    }
}