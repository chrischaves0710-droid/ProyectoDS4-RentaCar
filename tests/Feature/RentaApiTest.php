<?php

use App\Models\Cliente;
use App\Models\Estado;
use App\Models\User;
use App\Models\Vehiculo;
use Database\Seeders\EstadoSeeder;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(EstadoSeeder::class);

    Role::firstOrCreate([
        'name' => 'Gestor_Rentas',
        'guard_name' => 'web',
    ]);
});

function autenticarGestorDeRentas(): void
{
    $gestor = User::factory()->create();
    $gestor->assignRole('Gestor_Rentas');

    Sanctum::actingAs($gestor, ['*']);
}

it('devuelve 401 al consultar rentas sin token', function () {
    $this->getJson('/api/rentas')
        ->assertUnauthorized()
        ->assertJsonStructure(['message']);
});

it('crea una renta con un vehículo disponible y devuelve 201', function () {
    autenticarGestorDeRentas();

    $cliente = Cliente::factory()->create();
    $disponible = Estado::where('nombre', 'Disponible')->firstOrFail();
    $alquilado = Estado::where('nombre', 'Alquilado')->firstOrFail();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $disponible->id,
    ]);

    $respuesta = $this->postJson('/api/rentas', [
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-10-01',
        'fecha_fin' => '2026-10-04',
        'precio_diario' => 30000,
    ]);

    $respuesta
        ->assertCreated()
        ->assertJsonPath('data.monto_total', 90000)
        ->assertJsonPath('data.vehiculo.id', $vehiculo->id)
        ->assertHeader('Location');

    expect($vehiculo->fresh()->estado_id)->toBe($alquilado->id);
});

it('devuelve 409 al intentar alquilar un vehículo ocupado', function () {
    autenticarGestorDeRentas();

    $cliente = Cliente::factory()->create();
    $alquilado = Estado::where('nombre', 'Alquilado')->firstOrFail();

    $vehiculo = Vehiculo::factory()->create([
        'estado_id' => $alquilado->id,
    ]);

    $this->postJson('/api/rentas', [
        'cliente_id' => $cliente->id,
        'vehiculo_id' => $vehiculo->id,
        'fecha_inicio' => '2026-10-01',
        'fecha_fin' => '2026-10-04',
        'precio_diario' => 30000,
    ])
        ->assertStatus(409)
        ->assertJsonStructure(['message']);
});

it('devuelve 422 y errores por campo cuando faltan datos', function () {
    autenticarGestorDeRentas();

    $this->postJson('/api/rentas', [])
        ->assertUnprocessable()
        ->assertJsonStructure([
            'message',
            'errors' => [
                'cliente_id',
                'vehiculo_id',
                'fecha_inicio',
                'fecha_fin',
                'precio_diario',
            ],
        ]);
});