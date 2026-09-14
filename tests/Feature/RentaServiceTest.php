<?php

use App\Exceptions\RentaException;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\Vehiculo;
use App\Services\RentaService;
use Database\Seeders\EstadoSeeder;

// ==========================================
// PREPARACIÓN PARA CADA PRUEBA
// ==========================================

beforeEach(function () {
    $this->seed(EstadoSeeder::class);
});

// ==========================================
// PRUEBAS DE REGLAS DE NEGOCIO
// ==========================================

it('Regla 1: No permite alquilar un vehículo que no está disponible', function () {

    $cliente = Cliente::factory()->create();

    $estadoAlquilado = Estado::where('nombre', 'Alquilado')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoAlquilado->id,
    ]);

    $service = app(RentaService::class);

    $data = [
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(5)->toDateString(),
        'precio_diario' => 30000,
    ];

    expect(fn () => $service->crear($data))
        ->toThrow(RentaException::class);
});


it('Regla 2: No permite una renta con duración menor a un día', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    $service = app(RentaService::class);

    $data = [
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-09-20',
        'fecha_fin' => '2026-09-20',
        'precio_diario' => 30000,
    ];

    expect(fn () => $service->crear($data))
        ->toThrow(
            RentaException::class,
            'La renta debe tener una duración mínima de un día.'
        );
});


it('Regla 3: Calcula automáticamente el monto total de la renta', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    $service = app(RentaService::class);

    $renta = $service->crear([
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-09-15',
        'fecha_fin' => '2026-09-20',
        'precio_diario' => 30000,
    ]);

    expect((float) $renta->monto_total)
        ->toBe(150000.0);
});


it('Regla 4: Cambia el vehículo de Disponible a Alquilado al crear la renta', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();
    $estadoAlquilado = Estado::where('nombre', 'Alquilado')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    $service = app(RentaService::class);

    $service->crear([
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-09-15',
        'fecha_fin' => '2026-09-20',
        'precio_diario' => 30000,
    ]);

    $vehiculo->refresh();

    expect($vehiculo->estado_id)
        ->toBe($estadoAlquilado->id);
});


it('Regla 5: No permite actualizar una renta con fecha final anterior a la inicial', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    $renta = Renta::factory()->create([
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-09-15',
        'fecha_fin' => '2026-09-20',
        'precio_diario' => 30000,
        'monto_total' => 150000,
    ]);

    $service = app(RentaService::class);

    expect(fn () => $service->actualizar($renta->id, [
        'fecha_fin' => '2026-09-10',
    ]))->toThrow(
        RentaException::class,
        'La fecha final debe ser posterior a la fecha inicial.'
    );
});

// ==========================================
// PRUEBAS DE OPERACIÓN Y TRANSACCIONALIDAD
// ==========================================

it('recalcula el monto total al actualizar las fechas de una renta', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    $renta = Renta::factory()->create([
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-09-15',
        'fecha_fin' => '2026-09-20',
        'precio_diario' => 30000,
        'monto_total' => 150000,
    ]);

    $service = app(RentaService::class);

    $actualizada = $service->actualizar($renta->id, [
        'fecha_fin' => '2026-09-25',
    ]);

    expect((float) $actualizada->monto_total)
        ->toBe(300000.0);
});


it('hace rollback si no existe el estado Alquilado', function () {

    $cliente = Cliente::factory()->create();

    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $estadoDisponible->id,
    ]);

    Estado::where('nombre', 'Alquilado')->delete();

    $totalAntes = Renta::count();

    $service = app(RentaService::class);

    try {

        $service->crear([
            'cliente_id' => $cliente->id,
            'vehiculo_id' => $vehiculo->id,
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-09-20',
            'precio_diario' => 30000,
        ]);

    } catch (RentaException $e) {
        // Se espera la excepción
    }

    $vehiculo->refresh();

    expect(Renta::count())
        ->toBe($totalAntes);

    expect($vehiculo->estado_id)
        ->toBe($estadoDisponible->id);
});


it('lanza una excepción al buscar una renta inexistente', function () {

    $service = app(RentaService::class);

    expect(fn () => $service->obtener(999999))
        ->toThrow(
            RentaException::class,
            'Renta no encontrada.'
        );
});


it('elimina correctamente una renta existente', function () {

    $renta = Renta::factory()->create();

    $service = app(RentaService::class);

    $service->eliminar($renta->id);

    expect(Renta::find($renta->id))
        ->toBeNull();
});
