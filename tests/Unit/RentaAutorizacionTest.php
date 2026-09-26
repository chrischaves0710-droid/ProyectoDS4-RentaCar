<?php

use App\Models\User;
use App\Services\RentaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $cliente = Mockery::mock(User::class);

    $cliente->shouldReceive('hasAnyRole')
        ->once()
        ->with(['Admin_General', 'Gestor_Rentas'])
        ->andReturn(false);

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($cliente);
});

it('rechaza crear una renta sin rol de gestión', function () {
    expect(fn () => app(RentaService::class)->crear([]))
        ->toThrow(AuthorizationException::class);
});

it('rechaza actualizar una renta sin rol de gestión', function () {
    expect(fn () => app(RentaService::class)->actualizar(1, []))
        ->toThrow(AuthorizationException::class);
});

it('rechaza eliminar una renta sin rol de gestión', function () {
    expect(fn () => app(RentaService::class)->eliminar(1))
        ->toThrow(AuthorizationException::class);
});

it('rechaza finalizar una renta sin rol de gestión', function () {
    expect(fn () => app(RentaService::class)->finalizar(1))
        ->toThrow(AuthorizationException::class);
});