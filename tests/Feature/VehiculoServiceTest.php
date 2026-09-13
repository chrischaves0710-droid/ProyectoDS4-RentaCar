<?php

use App\Exceptions\VehiculoException;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Illuminate\Support\Facades\DB;

/**
 * @property \App\Models\Categoria $categoria
 * @property \App\Models\Estado $estadoDisponible
 * @property \App\Models\Estado $estadoParaVenta
 * @property \App\Services\VehiculoService $service
 */

beforeEach(function () {
    $this->seed(\Database\Seeders\EstadoSeeder::class);
    $this->categoria = Categoria::factory()->create();
    $this->estadoDisponible = Estado::where('nombre', 'Disponible')->first();
    $this->estadoParaVenta = Estado::where('nombre', 'Para Venta')->first();
    $this->service = app(VehiculoService::class);
});

/* -------------------------------------------------------------------------- */
/* EVALUACIÓN DE REGLAS DE NEGOCIO                                            */
/* -------------------------------------------------------------------------- */

it('Regla 1: Rechaza el registro inicial si el kilometraje supera los 5,000 km', function () {
    $data = [
        'placa' => 'ABC-123',
        'marca' => 'Toyota',
        'modelo' => 'Yaris',
        'anno' => 2024,
        'kilometraje' => 5001,
        'categoria_id' => $this->categoria->id,
    ];

    expect(fn () => $this->service->crear($data))
        ->toThrow(VehiculoException::class, 'No se permite el registro inicial de vehículos con más de 5,000 km.');
});

it('Regla 2: Asigna estado Para Venta si la antigüedad es mayor o igual a 10 años', function () {
    $annoActual = (int) date('Y');
    $data = [
        'placa' => 'OLD-999',
        'marca' => 'Nissan',
        'modelo' => 'Sentra',
        'anno' => $annoActual - 10,
        'kilometraje' => 3000,
        'categoria_id' => $this->categoria->id,
    ];

    $vehiculo = $this->service->crear($data);

    expect($vehiculo->estado_id)->toBe($this->estadoParaVenta->id)
        ->and($vehiculo->estado->nombre)->toBe('Para Venta');
});

it('Regla 3: Cambia el estado a Para Venta al actualizar si el kilometraje supera los 80,000 km', function () {
    $vehiculo = Vehiculo::factory()->create([
        'kilometraje' => 75000,
        'anno' => 2024,
        'categoria_id' => $this->categoria->id,
        'estado_id' => $this->estadoDisponible->id,
    ]);

    $vehiculoActualizado = $this->service->actualizar($vehiculo, [
        'kilometraje' => 85000,
    ]);

    expect($vehiculoActualizado->estado_id)->toBe($this->estadoParaVenta->id)
        ->and($vehiculoActualizado->estado->nombre)->toBe('Para Venta');
});

it('Regla 4: Impide reducir el kilometraje histórico registrado', function () {
    $vehiculo = Vehiculo::factory()->create([
        'kilometraje' => 10000,
        'categoria_id' => $this->categoria->id,
        'estado_id' => $this->estadoDisponible->id,
    ]);

    expect(fn () => $this->service->actualizar($vehiculo, ['kilometraje' => 9999]))
        ->toThrow(VehiculoException::class, 'El kilometraje (9999 km) no puede ser menor al histórico registrado (10000 km).');
});

it('Regla 5: Bloquea la eliminación si existen contratos de alquiler activos', function () {
    $vehiculo = Vehiculo::factory()->create([
        'categoria_id' => $this->categoria->id,
        'estado_id' => $this->estadoDisponible->id,
    ]);

    Renta::factory()->create([
        'vehiculo_id' => $vehiculo->id,
        'fecha_fin' => now()->addDays(5)->toDateString(),
    ]);

    expect(fn () => $this->service->eliminar($vehiculo))
        ->toThrow(VehiculoException::class, 'No se puede eliminar el vehículo porque tiene contratos de alquiler activos.');
});

/* -------------------------------------------------------------------------- */
/* TRANSACCIONES MULTITABLA Y REVERSIÓN (ACID)                                */
/* -------------------------------------------------------------------------- */

it('Transacción Multitabla: Crea el vehículo y registra la auditoría exitosamente', function () {
    $data = [
        'placa' => 'NEW-777',
        'marca' => 'Hyundai',
        'modelo' => 'Tucson',
        'anno' => 2024,
        'kilometraje' => 1000,
        'categoria_id' => $this->categoria->id,
    ];

    $vehiculo = $this->service->crear($data);

    $this->assertDatabaseHas('vehiculos', [
        'id' => $vehiculo->id,
        'placa' => 'NEW-777',
    ]);

    $this->assertDatabaseHas('auditoria_vehiculos', [
        'vehiculo_id' => $vehiculo->id,
        'accion' => 'REGISTRO_INICIAL',
    ]);
});

it('Transacción Rollback: Revierte la inserción del vehículo si ocurre un fallo interno', function () {
    try {
        DB::transaction(function () {
            Vehiculo::create([
                'placa' => 'FAIL-999',
                'marca' => 'Honda',
                'modelo' => 'Civic',
                'anno' => 2024,
                'kilometraje' => 1000,
                'categoria_id' => $this->categoria->id,
                'estado_id' => $this->estadoDisponible->id,
            ]);

            // Simulación de fallo dentro del bloque transaccional
            throw new Exception('Error simulado en paso secundario de auditoría');
        });
    } catch (Exception $e) {
        // Excepción capturada para evaluar el rollback
    }

    $this->assertDatabaseMissing('vehiculos', ['placa' => 'FAIL-999']);
});