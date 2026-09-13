<?php

use App\Exceptions\VehiculoException;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Renta;
use App\Models\Vehiculo;
use App\Services\VehiculoService;
use Database\Seeders\EstadoSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(EstadoSeeder::class);
});

// ==========================================
// PRUEBAS DE REGLAS DE NEGOCIO
// ==========================================

it('Regla 1: Rechaza el registro inicial si el kilometraje supera los 5,000 km', function () {
    $categoria = Categoria::factory()->create();
    $service = app(VehiculoService::class);

    $data = [
        'placa' => 'ABC-123',
        'marca' => 'Toyota',
        'modelo' => 'Yaris',
        'anno' => (int) date('Y'),
        'kilometraje' => 5001,
        'precio_diario' => 35.00,
        'categoria_id' => $categoria->id,
    ];

    expect(fn () => $service->crear($data))
        ->toThrow(VehiculoException::class, 'No se permite el registro inicial de vehículos con más de 5,000 km.');
});

it('Regla 2: Asigna automáticamente el estado Para Venta si tiene 10 o más años de antigüedad', function () {
    $categoria = Categoria::factory()->create();
    $service = app(VehiculoService::class);
    $annoAntiguo = (int) date('Y') - 10;

    $data = [
        'placa' => 'VIE-100',
        'marca' => 'Nissan',
        'modelo' => 'Sentra',
        'anno' => $annoAntiguo,
        'kilometraje' => 3000,
        'precio_diario' => 25.00,
        'categoria_id' => $categoria->id,
    ];

    $vehiculo = $service->crear($data);
    $estadoParaVenta = Estado::where('nombre', 'Para Venta')->first();

    expect($vehiculo->estado_id)->toBe($estadoParaVenta->id);
});

it('Regla 3: Asigna automáticamente el estado Disponible en un registro nuevo estándar', function () {
    $categoria = Categoria::factory()->create();
    $service = app(VehiculoService::class);

    $data = [
        'placa' => 'NUE-200',
        'marca' => 'Toyota',
        'modelo' => 'Corolla',
        'anno' => (int) date('Y'),
        'kilometraje' => 1500,
        'precio_diario' => 40.00,
        'categoria_id' => $categoria->id,
    ];

    $vehiculo = $service->crear($data);
    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    expect($vehiculo->estado_id)->toBe($estadoDisponible->id);
});

it('Regla 4: No permite actualizar el vehículo con un kilometraje menor al histórico registrado', function () {
    $categoria = Categoria::factory()->create();
    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'kilometraje' => 3000,
        'categoria_id' => $categoria->id,
        'estado_id' => $estadoDisponible->id,
    ]);

    $service = app(VehiculoService::class);

    $dataActualizada = array_merge($vehiculo->toArray(), [
        'kilometraje' => 2000,
    ]);

    expect(fn () => $service->actualizar($vehiculo, $dataActualizada))
        ->toThrow(VehiculoException::class);
});

it('Regla 5: Bloquea la eliminación si el vehículo tiene contratos de alquiler activos', function () {
    $categoria = Categoria::factory()->create();
    $estadoDisponible = Estado::where('nombre', 'Disponible')->first();

    $vehiculo = Vehiculo::factory()->create([
        'categoria_id' => $categoria->id,
        'estado_id' => $estadoDisponible->id,
    ]);

    Renta::factory()->create([
        'vehiculo_id' => $vehiculo->id,
        'fecha_fin' => now()->addDays(5),
    ]);

    $service = app(VehiculoService::class);

    expect(fn () => $service->eliminar($vehiculo))
        ->toThrow(VehiculoException::class);
});

// ==========================================
// PRUEBAS DE OPERACIÓN Y TRANSACCIONALIDAD
// ==========================================

it('aumenta el total de vehículos en 1 tras una creación exitosa', function () {
    $totalAntes = Vehiculo::count();
    $categoria = Categoria::factory()->create();

    app(VehiculoService::class)->crear([
        'placa' => 'ADD-001',
        'marca' => 'Hyundai',
        'modelo' => 'Elantra',
        'anno' => (int) date('Y'),
        'kilometraje' => 1000,
        'precio_diario' => 30.00,
        'categoria_id' => $categoria->id,
    ]);

    expect(Vehiculo::count())->toBe($totalAntes + 1);
});

it('registra una entrada en la tabla de auditoría dentro de la transacción al crear', function () {
    $categoria = Categoria::factory()->create();
    $service = app(VehiculoService::class);

    $vehiculo = $service->crear([
        'placa' => 'AUD-777',
        'marca' => 'Kia',
        'modelo' => 'Sportage',
        'anno' => (int) date('Y'),
        'kilometraje' => 2500,
        'precio_diario' => 50.00,
        'categoria_id' => $categoria->id,
    ]);

    $existeAuditoria = DB::table('auditoria_vehiculos')
        ->where('vehiculo_id', $vehiculo->id)
        ->where('accion', 'REGISTRO_INICIAL')
        ->exists();

    expect($existeAuditoria)->toBeTrue();
});