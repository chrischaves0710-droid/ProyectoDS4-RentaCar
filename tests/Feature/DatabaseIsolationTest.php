<?php

use App\Models\Cliente;

it('ejecuta las pruebas en ambiente testing', function () {
    expect(app()->environment())->toBe('testing');
});

it('utiliza sqlite como conexion de base de datos durante las pruebas', function () {
    expect(config('database.default'))->toBe('sqlite');
});

it('utiliza una base de datos sqlite en memoria', function () {
    expect(config('database.connections.sqlite.database'))
        ->toBe(':memory:');
});

it('puede escribir datos dentro de la base aislada de pruebas', function () {
    $cliente = Cliente::factory()->create();

    $this->assertDatabaseHas('clientes', [
        'id' => $cliente->id,
        'correo' => $cliente->correo,
    ]);
});