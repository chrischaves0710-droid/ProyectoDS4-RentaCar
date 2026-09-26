<?php

use App\Models\Estado;
use App\Models\Renta;
use App\Models\User;
use Database\Seeders\EstadoSeeder;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(EstadoSeeder::class);

    foreach (['Gestor_Rentas', 'Cliente'] as $nombre) {
        Role::firstOrCreate([
            'name' => $nombre,
            'guard_name' => 'web',
        ]);
    }
});

it('permite a un gestor finalizar una renta y libera el vehículo', function () {
    $estadoAlquilado = Estado::where('nombre', 'Alquilado')->firstOrFail();
    $estadoDisponible = Estado::where('nombre', 'Disponible')->firstOrFail();

    $renta = Renta::factory()->create();
    $renta->vehiculo->update(['estado_id' => $estadoAlquilado->id]);

    $gestor = User::factory()->create();
    $gestor->assignRole('Gestor_Rentas');

    Sanctum::actingAs($gestor, ['*']);

    $this->patchJson("/api/rentas/{$renta->id}/finalizar")
        ->assertOk()
        ->assertJsonPath('data.id', $renta->id)
        ->assertJsonPath('data.vehiculo.id', $renta->vehiculo_id);

    expect($renta->vehiculo->fresh()->estado_id)
        ->toBe($estadoDisponible->id);
});

it('impide que un cliente finalice una renta', function () {
    $estadoAlquilado = Estado::where('nombre', 'Alquilado')->firstOrFail();

    $renta = Renta::factory()->create();
    $renta->vehiculo->update(['estado_id' => $estadoAlquilado->id]);

    $cliente = User::factory()->create();
    $cliente->assignRole('Cliente');

    Sanctum::actingAs($cliente, ['*']);

    $this->patchJson("/api/rentas/{$renta->id}/finalizar")
        ->assertForbidden()
        ->assertJsonStructure(['message']);

    expect($renta->vehiculo->fresh()->estado_id)
        ->toBe($estadoAlquilado->id);
});