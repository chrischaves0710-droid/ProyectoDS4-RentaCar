<?php

use App\Exceptions\EstadoException;
use App\Models\Estado;
use App\Models\User;
use App\Models\Vehiculo;
use App\Services\EstadoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::create([
        'name' => 'Admin Estado Test',
        'email' => 'admin_estado_' . uniqid() . '@correo.com',
        'password' => 'Password123',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Admin_General',
        'guard_name' => 'web',
    ]);

    $admin->assignRole($role);
    $this->actingAs($admin);
});

it('puede crear un estado', function () {
    $data = ['nombre' => 'Nuevo Estado Prueba'];

    $estado = app(EstadoService::class)->crear($data);

    expect($estado->nombre)->toBe('Nuevo Estado Prueba');

    $this->assertDatabaseHas('estados', [
        'nombre' => 'Nuevo Estado Prueba'
    ]);
});

it('puede actualizar un estado existente', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'Viejo Estado'
    ]);

    $data = ['nombre' => 'Estado Actualizado'];

    app(EstadoService::class)->actualizar($estado, $data);

    expect($estado->fresh()->nombre)->toBe('Estado Actualizado');
});

it('permite eliminar un estado si no tiene vehiculos asociados', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'Estado Sin Uso'
    ]);

    app(EstadoService::class)->eliminar($estado);

    $this->assertDatabaseMissing('estados', [
        'id' => $estado->id
    ]);
});

it('lanza una excepcion si se intenta eliminar un estado con vehiculos asociados', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'En Uso'
    ]);

    // Creamos un vehículo asignado a este estado
    Vehiculo::factory()->create([
        'estado_id' => $estado->id
    ]);

    expect(fn () => app(EstadoService::class)->eliminar($estado))
        ->toThrow(
            EstadoException::class,
            'No se puede eliminar el estado porque tiene vehículos asociados.'
        );

    // Verificamos que el estado no fue eliminado
    $this->assertDatabaseHas('estados', [
        'id' => $estado->id
    ]);
});

it('rechaza una invocacion directa al servicio de estado sin un rol autorizado', function () {

    $usuario = User::create([
        'name' => 'Gestor Sin Permiso Estado',
        'email' => 'gestor_estado_' . uniqid() . '@correo.com',
        'password' => 'Password123',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Gestor_Rentas',
        'guard_name' => 'web',
    ]);

    $usuario->assignRole($role);

    $this->actingAs($usuario);

    expect(
        fn () => app(EstadoService::class)->crear([
            'nombre' => 'Estado No Autorizado'
        ])
    )->toThrow(AuthorizationException::class);

    $this->assertDatabaseMissing('estados', [
        'nombre' => 'Estado No Autorizado'
    ]);
});