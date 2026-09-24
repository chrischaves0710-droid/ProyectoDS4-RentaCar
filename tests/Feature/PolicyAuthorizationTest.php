<?php

use App\Models\Cliente;
use App\Models\Renta;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

/**
 * Crea un usuario y le asigna uno de los roles del sistema.
 */
function crearUsuarioParaPolicy(string $rol, ?string $email = null): User
{
    $user = User::create([
        'name' => 'Usuario ' . $rol,
        'email' => $email ?? strtolower($rol) . '_' . uniqid() . '@correo.com',
        'password' => 'Password123!',
    ]);

    $role = Role::firstOrCreate([
        'name' => $rol,
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    return $user;
}

/*
|--------------------------------------------------------------------------
| POLICIES DE CLIENTE
|--------------------------------------------------------------------------
*/

it('Admin_General puede consultar cualquier cliente', function () {
    $cliente = Cliente::factory()->create();

    $usuario = crearUsuarioParaPolicy('Admin_General');

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson("/api/clientes/{$cliente->id}");

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
        ]);
});

it('Gestor_Rentas puede consultar clientes', function () {
    $cliente = Cliente::factory()->create();

    $usuario = crearUsuarioParaPolicy('Gestor_Rentas');

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson("/api/clientes/{$cliente->id}");

    $response->assertStatus(200);
});

it('Cliente puede consultar su propio registro', function () {
    $cliente = Cliente::factory()->create();

    $usuario = crearUsuarioParaPolicy(
        'Cliente',
        $cliente->correo
    );

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson("/api/clientes/{$cliente->id}");

    $response->assertStatus(200);
});

it('Cliente no puede consultar el registro de otro cliente', function () {
    $clienteAjeno = Cliente::factory()->create();

    $usuario = crearUsuarioParaPolicy(
        'Cliente',
        'cliente_propietario_' . uniqid() . '@correo.com'
    );

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson(
        "/api/clientes/{$clienteAjeno->id}"
    );

    $response->assertStatus(403);
});

it('Cliente no puede listar todos los clientes', function () {
    Cliente::factory()->count(2)->create();

    $usuario = crearUsuarioParaPolicy(
        'Cliente',
        'cliente_' . uniqid() . '@correo.com'
    );

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson('/api/clientes');

    $response->assertStatus(403);
});


/*
|--------------------------------------------------------------------------
| POLICIES DE RENTA
|--------------------------------------------------------------------------
*/

it('Cliente puede consultar una renta que le pertenece', function () {
    $cliente = Cliente::factory()->create();

    $renta = Renta::factory()->create([
        'cliente_id' => $cliente->id,
    ]);

    $usuario = crearUsuarioParaPolicy(
        'Cliente',
        $cliente->correo
    );

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson("/api/rentas/{$renta->id}");

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data',
        ]);
});

it('Cliente no puede consultar una renta de otro cliente', function () {
    $clientePropio = Cliente::factory()->create();
    $clienteAjeno = Cliente::factory()->create();

    $rentaAjena = Renta::factory()->create([
        'cliente_id' => $clienteAjeno->id,
    ]);

    $usuario = crearUsuarioParaPolicy(
        'Cliente',
        $clientePropio->correo
    );

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson(
        "/api/rentas/{$rentaAjena->id}"
    );

    $response->assertStatus(403);
});


/*
|--------------------------------------------------------------------------
| CAPA 1 - ROL EN LA RUTA
|--------------------------------------------------------------------------
*/

it('Admin_Inventarios no puede acceder a los endpoints de clientes', function () {
    $cliente = Cliente::factory()->create();

    $usuario = crearUsuarioParaPolicy('Admin_Inventarios');

    Sanctum::actingAs($usuario, ['*']);

    $response = $this->getJson("/api/clientes/{$cliente->id}");

    $response->assertStatus(403);
});