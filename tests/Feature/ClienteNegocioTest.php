```php
<?php

use App\Exceptions\ClienteException;
use App\Models\Cliente;
use App\Models\Renta;
use App\Services\ClienteService;

/* -------------------------------------------------------------------------- */
/* REGLAS DE NEGOCIO                                                         */
/* -------------------------------------------------------------------------- */

it('Regla 1: Impide registrar un cliente menor de edad', function () {
    $service = app(ClienteService::class);

    $data = [
        'cedula' => '123456789',
        'nombre1' => 'Juan',
        'nombre2' => null,
        'apellido1' => 'Pérez',
        'apellido2' => null,
        'anno_nacimiento' => date('Y') - 17,
        'telefono' => '88888888',
        'correo' => 'juan@example.com',
    ];

    expect(fn () => $service->crear($data))
        ->toThrow(
            ClienteException::class,
            'El cliente debe ser mayor de edad.'
        );
});

it('Regla 2: Impide eliminar un cliente que tiene rentas asociadas', function () {
    $cliente = Cliente::factory()->create();

    Renta::factory()->create([
        'cliente_id' => $cliente->id,
    ]);

    $service = app(ClienteService::class);

    expect(fn () => $service->eliminar($cliente))
        ->toThrow(
            ClienteException::class,
            'No se puede eliminar el cliente porque tiene rentas asociadas.'
        );

    expect(Cliente::find($cliente->id))->not->toBeNull();
});

/* -------------------------------------------------------------------------- */
/* CRUD                                                                      */
/* -------------------------------------------------------------------------- */

it('permite crear un cliente correctamente', function () {
    $service = app(ClienteService::class);

    $data = [
        'cedula' => '987654321',
        'nombre1' => 'Carlos',
        'nombre2' => null,
        'apellido1' => 'Rodríguez',
        'apellido2' => null,
        'anno_nacimiento' => 1995,
        'telefono' => '88888888',
        'correo' => 'carlos@example.com',
    ];

    $cliente = $service->crear($data);

    expect($cliente)->toBeInstanceOf(Cliente::class);

    expect(
        Cliente::where('cedula', '987654321')->exists()
    )->toBeTrue();
});

it('permite actualizar un cliente correctamente', function () {
    $cliente = Cliente::factory()->create([
        'anno_nacimiento' => 1995,
    ]);

    $service = app(ClienteService::class);

    $clienteActualizado = $service->actualizar($cliente, [
        'cedula' => $cliente->cedula,
        'nombre1' => 'Carlos',
        'nombre2' => $cliente->nombre2,
        'apellido1' => $cliente->apellido1,
        'apellido2' => $cliente->apellido2,
        'anno_nacimiento' => $cliente->anno_nacimiento,
        'telefono' => '89999999',
        'correo' => $cliente->correo,
    ]);

    expect($clienteActualizado->telefono)->toBe('89999999');
    expect($clienteActualizado->nombre1)->toBe('Carlos');
});

it('permite eliminar un cliente sin rentas asociadas', function () {
    $service = app(ClienteService::class);

    $cliente = Cliente::factory()->create();

    $resultado = $service->eliminar($cliente);

    expect($resultado)->toBeTrue();
    expect(Cliente::find($cliente->id))->toBeNull();
});

/* -------------------------------------------------------------------------- */
/* LISTADO                                                                    */
/* -------------------------------------------------------------------------- */

it('permite listar clientes con paginación y límite máximo', function () {
    $service = app(ClienteService::class);

    Cliente::factory()->count(10)->create();

    $resultado = $service->listar(
        null,
        'nombre1',
        'asc',
        100
    );

    expect($resultado->perPage())->toBe(50);
});
