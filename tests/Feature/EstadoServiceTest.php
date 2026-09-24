<?php

use App\Exceptions\EstadoException;
use App\Models\Estado;
use App\Models\User;
use App\Models\Vehiculo;
use App\Services\EstadoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

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
    $data = [
        'nombre' => 'Nuevo Estado Prueba'
    ];

    $estado = app(EstadoService::class)->crear($data);

    expect($estado->nombre)
        ->toBe('Nuevo Estado Prueba');

    $this->assertDatabaseHas('estados', [
        'nombre' => 'Nuevo Estado Prueba'
    ]);
});


it('puede actualizar un estado existente', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'Viejo Estado'
    ]);

    $data = [
        'nombre' => 'Estado Actualizado'
    ];

    app(EstadoService::class)
        ->actualizar($estado, $data);

    expect($estado->fresh()->nombre)
        ->toBe('Estado Actualizado');
});


it('permite eliminar un estado si no tiene vehiculos asociados', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'Estado Sin Uso'
    ]);

    app(EstadoService::class)
        ->eliminar($estado);

    $this->assertDatabaseMissing('estados', [
        'id' => $estado->id
    ]);
});


it('lanza una excepcion si se intenta eliminar un estado con vehiculos asociados', function () {
    $estado = Estado::factory()->create([
        'nombre' => 'En Uso'
    ]);

    Vehiculo::factory()->create([
        'estado_id' => $estado->id
    ]);

    expect(
        fn () => app(EstadoService::class)
            ->eliminar($estado)
    )->toThrow(
        EstadoException::class,
        'No se puede eliminar el estado porque tiene vehículos asociados.'
    );

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


function crearEstadoServiceConDobles(): array
{
    $estadoModel = Mockery::mock(Estado::class);
    $vehiculoModel = Mockery::mock(Vehiculo::class);

    $service = new EstadoService(
        $estadoModel,
        $vehiculoModel
    );

    return [
        'estadoModel' => $estadoModel,
        'vehiculoModel' => $vehiculoModel,
        'service' => $service,
    ];
}


function simularUsuarioAutorizadoEstado(): User
{
    $usuario = Mockery::mock(User::class);

    $usuario
        ->shouldReceive('hasAnyRole')
        ->once()
        ->with([
            'Admin_General',
            'Admin_Inventarios',
        ])
        ->andReturn(true);

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($usuario);

    return $usuario;
}


it('crea un estado utilizando un doble del modelo Estado', function () {
    $dependencias = crearEstadoServiceConDobles();

    $estadoModel = $dependencias['estadoModel'];
    $service = $dependencias['service'];

    simularUsuarioAutorizadoEstado();

    $data = [
        'nombre' => 'Disponible'
    ];

    $query = Mockery::mock();

    $estadoCreado = Mockery::mock(
        Estado::class
    );

    $estadoModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('create')
        ->once()
        ->with($data)
        ->andReturn($estadoCreado);

    $resultado = $service->crear($data);

    expect($resultado)
        ->toBe($estadoCreado);
});


it('actualiza un estado utilizando un doble del recurso', function () {
    $dependencias = crearEstadoServiceConDobles();

    $service = $dependencias['service'];

    simularUsuarioAutorizadoEstado();

    $estado = Mockery::mock(
        Estado::class
    );

    $data = [
        'nombre' => 'Mantenimiento'
    ];

    $estado
        ->shouldReceive('update')
        ->once()
        ->with($data)
        ->andReturn(true);

    $resultado = $service->actualizar(
        $estado,
        $data
    );

    expect($resultado)
        ->toBe($estado);
});


it('elimina un estado sin vehiculos usando un doble de Vehiculo', function () {
    $vehiculoModel = Mockery::mock(Vehiculo::class);

    $service = new EstadoService(
        new Estado(),
        $vehiculoModel
    );

    simularUsuarioAutorizadoEstado();

    // Estado real para evitar conflictos entre Mockery y Eloquent.
    $estado = Estado::factory()->create([
        'nombre' => 'Estado Sin Vehiculos Mock'
    ]);

    $query = Mockery::mock();

    $vehiculoModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('where')
        ->once()
        ->with(
            'estado_id',
            $estado->id
        )
        ->andReturnSelf();

    $query
        ->shouldReceive('exists')
        ->once()
        ->andReturn(false);

    $service->eliminar($estado);

    $this->assertDatabaseMissing('estados', [
        'id' => $estado->id
    ]);
});

it('rechaza eliminar un estado con vehiculos usando un doble', function () {
    $vehiculoModel = Mockery::mock(Vehiculo::class);

    $service = new EstadoService(
        new Estado(),
        $vehiculoModel
    );

    simularUsuarioAutorizadoEstado();

    // Estado real.
    $estado = Estado::factory()->create([
        'nombre' => 'Estado Con Vehiculos Mock'
    ]);

    $query = Mockery::mock();

    $vehiculoModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('where')
        ->once()
        ->with(
            'estado_id',
            $estado->id
        )
        ->andReturnSelf();

    $query
        ->shouldReceive('exists')
        ->once()
        ->andReturn(true);

    expect(
        fn () => $service->eliminar($estado)
    )->toThrow(
        EstadoException::class,
        'No se puede eliminar el estado porque tiene vehículos asociados.'
    );

    // La regla de negocio debe impedir la eliminación.
    $this->assertDatabaseHas('estados', [
        'id' => $estado->id
    ]);
});

it('rechaza mediante doble un usuario con rol no permitido', function () {
    $dependencias = crearEstadoServiceConDobles();

    $service = $dependencias['service'];

    $usuario = Mockery::mock(
        User::class
    );

    $usuario
        ->shouldReceive('hasAnyRole')
        ->once()
        ->with([
            'Admin_General',
            'Admin_Inventarios',
        ])
        ->andReturn(false);

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($usuario);

    expect(
        fn () => $service->crear([
            'nombre' => 'No Permitido'
        ])
    )->toThrow(
        AuthorizationException::class
    );
});


it('rechaza la operacion cuando no existe usuario autenticado', function () {
    $dependencias = crearEstadoServiceConDobles();

    $service = $dependencias['service'];

    Auth::shouldReceive('user')
        ->once()
        ->andReturn(null);

    expect(
        fn () => $service->crear([
            'nombre' => 'Sin Usuario'
        ])
    )->toThrow(
        AuthorizationException::class
    );
});


it('limita per page a 50 cuando recibe un valor superior', function () {
    $dependencias = crearEstadoServiceConDobles();

    $estadoModel = $dependencias['estadoModel'];
    $service = $dependencias['service'];

    simularUsuarioAutorizadoEstado();

    $query = Mockery::mock();
    $paginator = Mockery::mock();

    $estadoModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('orderBy')
        ->once()
        ->with('id', 'asc')
        ->andReturnSelf();

    $query
        ->shouldReceive('paginate')
        ->once()
        ->with(50)
        ->andReturn($paginator);

    $paginator
        ->shouldReceive('withQueryString')
        ->once()
        ->andReturnSelf();

    $resultado = $service->listar([
        'per_page' => 100
    ]);

    expect($resultado)
        ->toBe($paginator);
});


it('corrige sort y direction invalidos al listar estados', function () {
    $dependencias = crearEstadoServiceConDobles();

    $estadoModel = $dependencias['estadoModel'];
    $service = $dependencias['service'];

    simularUsuarioAutorizadoEstado();

    $query = Mockery::mock();
    $paginator = Mockery::mock();

    $estadoModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('orderBy')
        ->once()
        ->with(
            'id',
            'asc'
        )
        ->andReturnSelf();

    $query
        ->shouldReceive('paginate')
        ->once()
        ->with(15)
        ->andReturn($paginator);

    $paginator
        ->shouldReceive('withQueryString')
        ->once()
        ->andReturnSelf();

    $resultado = $service->listar([
        'sort' => 'campo_falso',
        'direction' => 'lado'
    ]);

    expect($resultado)
        ->toBe($paginator);
});