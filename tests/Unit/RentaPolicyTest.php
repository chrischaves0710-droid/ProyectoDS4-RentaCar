<?php

use App\Models\Cliente;
use App\Models\Renta;
use App\Models\User;
use App\Policies\RentaPolicy;
use Mockery;

function rentaConCliente(?Cliente $cliente): Renta
{
    /** @var Renta&\Mockery\MockInterface $renta */
    $renta = Mockery::mock(Renta::class)->makePartial();

    $renta->shouldReceive('getAttribute')
        ->with('cliente')
        ->andReturn($cliente);

    return $renta;
}

it('permite consultar una renta cuando el correo corresponde al cliente', function () {
    $usuario = new User(['email' => 'eddier@example.com']);
    $cliente = new Cliente(['correo' => 'eddier@example.com']);

    expect((new RentaPolicy())->view(
        $usuario,
        rentaConCliente($cliente)
    ))->toBeTrue();
});

it('compara los correos sin distinguir mayúsculas', function () {
    $usuario = new User(['email' => 'EDDIER@example.com']);
    $cliente = new Cliente(['correo' => 'eddier@example.com']);

    expect((new RentaPolicy())->view(
        $usuario,
        rentaConCliente($cliente)
    ))->toBeTrue();
});

it('rechaza consultar la renta de otro cliente', function () {
    $usuario = new User(['email' => 'eddier@example.com']);
    $cliente = new Cliente(['correo' => 'otra@example.com']);

    expect((new RentaPolicy())->view(
        $usuario,
        rentaConCliente($cliente)
    ))->toBeFalse();
});

it('rechaza una renta sin cliente asociado', function () {
    $usuario = new User(['email' => 'eddier@example.com']);

    expect((new RentaPolicy())->view(
        $usuario,
        rentaConCliente(null)
    ))->toBeFalse();
});