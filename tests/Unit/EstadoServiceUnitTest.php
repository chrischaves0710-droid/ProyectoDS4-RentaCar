<?php

use App\Exceptions\EstadoException;
use App\Models\Estado;
use App\Models\User;
use App\Models\Vehiculo;
use App\Services\EstadoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

function autorizarEstadoConDoble(bool $permitido = true): void
{
    $user = Mockery::mock(User::class);
    $user->shouldReceive('hasAnyRole')
        ->once()
        ->with(['Admin_General', 'Admin_Inventarios'])
        ->andReturn($permitido);

    Auth::shouldReceive('user')->once()->andReturn($user);
}

beforeEach(function () {
    $this->estadoModel = Mockery::mock(Estado::class);
    $this->vehiculoModel = Mockery::mock(Vehiculo::class);
    $this->service = new EstadoService(
        $this->estadoModel,
        $this->vehiculoModel
    );
});

it('crea un estado cuando el usuario tiene un rol autorizado', function () {
    autorizarEstadoConDoble();

    $data = ['nombre' => 'Disponible'];
    $query = Mockery::mock();
    $creado = Mockery::mock(Estado::class);

    $this->estadoModel->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query->shouldReceive('create')
        ->once()
        ->with($data)
        ->andReturn($creado);

    expect($this->service->crear($data))->toBe($creado);
});

it('actualiza un estado cuando el usuario tiene un rol autorizado', function () {
    autorizarEstadoConDoble();

    $estado = Mockery::mock(Estado::class);
    $data = ['nombre' => 'Mantenimiento'];

    $estado->shouldReceive('update')
        ->once()
        ->with($data)
        ->andReturnTrue();

    expect($this->service->actualizar($estado, $data))->toBe($estado);
});

it('elimina un estado cuando no tiene vehiculos asociados', function () {
    autorizarEstadoConDoble();

    $estado = Mockery::mock(Estado::class)->makePartial();
    $estado->id = 10;
    $query = Mockery::mock();

    $this->vehiculoModel->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query->shouldReceive('where')
        ->once()
        ->with('estado_id', 10)
        ->andReturnSelf();

    $query->shouldReceive('exists')
        ->once()
        ->andReturnFalse();

    $estado->shouldReceive('delete')->once()->andReturnTrue();

    $this->service->eliminar($estado);

    expect(true)->toBeTrue();
});

it('rechaza eliminar un estado que tiene vehiculos asociados', function () {
    autorizarEstadoConDoble();

    $estado = Mockery::mock(Estado::class)->makePartial();
    $estado->id = 20;
    $query = Mockery::mock();

    $this->vehiculoModel->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query->shouldReceive('where')
        ->once()
        ->with('estado_id', 20)
        ->andReturnSelf();

    $query->shouldReceive('exists')
        ->once()
        ->andReturnTrue();

    $estado->shouldNotReceive('delete');

    expect(fn () => $this->service->eliminar($estado))
        ->toThrow(
            EstadoException::class,
            'No se puede eliminar el estado porque tiene vehículos asociados.'
        );
});

it('rechaza la llamada directa al servicio con un rol no autorizado', function () {
    autorizarEstadoConDoble(false);

    $this->estadoModel->shouldNotReceive('newQuery');

    expect(fn () => $this->service->crear([
        'nombre' => 'No permitido',
    ]))->toThrow(AuthorizationException::class);
});

it('rechaza la llamada directa al servicio cuando no hay usuario autenticado', function () {
    Auth::shouldReceive('user')->once()->andReturnNull();

    $this->estadoModel->shouldNotReceive('newQuery');

    expect(fn () => $this->service->crear([
        'nombre' => 'Sin usuario',
    ]))->toThrow(AuthorizationException::class);
});