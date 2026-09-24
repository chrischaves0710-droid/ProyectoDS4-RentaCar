<?php

use App\Models\Cliente;
use App\Models\Renta;
use App\Models\User;
use App\Services\ClienteService;
use App\Services\RentaService;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Role;

function usuarioConRolServicio(string $rol, ?string $email = null): User
{
    Role::firstOrCreate([
        'name' => $rol,
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'email' => $email ?? fake()->unique()->safeEmail(),
    ]);

    $user->assignRole($rol);

    return $user;
}

test('cliente no puede listar todos los clientes llamando directamente al service', function () {
    $user = usuarioConRolServicio('Cliente');

    $this->actingAs($user);

    app(ClienteService::class)->listar();
})->throws(AuthorizationException::class);


test('gestor rentas no puede crear clientes llamando directamente al service', function () {
    $user = usuarioConRolServicio('Gestor_Rentas');

    $this->actingAs($user);

    app(ClienteService::class)->crear([
        'cedula' => '123456789',
        'nombre1' => 'Carlos',
        'nombre2' => null,
        'apellido1' => 'Mora',
        'apellido2' => 'Rojas',
        'anno_nacimiento' => 2000,
        'telefono' => '88888888',
        'correo' => 'carlos@example.com',
    ]);
})->throws(AuthorizationException::class);


test('cliente no puede crear rentas llamando directamente al service', function () {
    $user = usuarioConRolServicio('Cliente');

    $this->actingAs($user);

    app(RentaService::class)->crear([]);
})->throws(AuthorizationException::class);


test('admin inventarios no puede consultar rentas llamando directamente al service', function () {
    $user = usuarioConRolServicio('Admin_Inventarios');

    $this->actingAs($user);

    app(RentaService::class)->listar([]);
})->throws(AuthorizationException::class);


test('cliente puede obtener directamente su propio registro mediante el service', function () {
    $user = usuarioConRolServicio(
        'Cliente',
        'cliente@example.com'
    );

    $cliente = Cliente::factory()->create([
        'correo' => 'cliente@example.com',
    ]);

    $this->actingAs($user);

    $resultado = app(ClienteService::class)
        ->obtener($cliente);

    expect($resultado->id)->toBe($cliente->id);
});


test('cliente no puede obtener directamente el registro de otro cliente', function () {
    $user = usuarioConRolServicio(
        'Cliente',
        'cliente@example.com'
    );

    $otroCliente = Cliente::factory()->create([
        'correo' => 'otra@example.com',
    ]);

    $this->actingAs($user);

    app(ClienteService::class)
        ->obtener($otroCliente);
})->throws(AuthorizationException::class);


test('cliente puede obtener directamente una renta propia', function () {
    $user = usuarioConRolServicio(
        'Cliente',
        'cliente@example.com'
    );

    $cliente = Cliente::factory()->create([
        'correo' => 'cliente@example.com',
    ]);

    $renta = Renta::factory()->create([
        'cliente_id' => $cliente->id,
    ]);

    $this->actingAs($user);

    $resultado = app(RentaService::class)
        ->obtener($renta->id);

    expect($resultado->id)->toBe($renta->id);
});


test('cliente no puede obtener directamente una renta de otro cliente', function () {
    $user = usuarioConRolServicio(
        'Cliente',
        'cliente@example.com'
    );

    $otroCliente = Cliente::factory()->create([
        'correo' => 'otra@example.com',
    ]);

    $renta = Renta::factory()->create([
        'cliente_id' => $otroCliente->id,
    ]);

    $this->actingAs($user);

    app(RentaService::class)
        ->obtener($renta->id);
})->throws(AuthorizationException::class);