<?php

use App\Exceptions\AccesorioException;
use App\Models\Accesorio;
use App\Models\Renta;
use App\Models\User;
use App\Services\AccesorioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $admin = User::create([
        'name' => 'Admin Accesorio Test',
        'email' => 'admin_accesorio_' . uniqid() . '@correo.com',
        'password' => 'Password123',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Admin_General',
        'guard_name' => 'web',
    ]);

    $admin->assignRole($role);

    $this->actingAs($admin);
});


it('no permite eliminar un accesorio con rentas asociadas', function () {
    $accesorio = Accesorio::factory()->create();

    $renta = Renta::factory()->create();

    $renta->accesorios()->attach(
        $accesorio->id,
        [
            'cantidad' => 1,
            'precio_diario' => $accesorio->precio_unitario,
            'subtotal' => $accesorio->precio_unitario,
        ]
    );

    expect(
        fn () => app(AccesorioService::class)
            ->eliminar($accesorio)
    )->toThrow(
        AccesorioException::class
    );

    expect(
        Accesorio::find($accesorio->id)
    )->not->toBeNull();
});


it('no permite agregar un accesorio a una renta finalizada', function () {
    $accesorio = Accesorio::factory()->create([
        'precio_unitario' => 1000
    ]);

    $renta = Renta::factory()->create([
        'fecha_fin' => now()->subDay(),
        'monto_total' => 10000,
    ]);

    expect(
        fn () => app(AccesorioService::class)
            ->agregarARenta(
                $accesorio,
                $renta,
                1
            )
    )->toThrow(
        AccesorioException::class
    );

    expect(
        DB::table('accesorio_renta')->count()
    )->toBe(0);
});


it('no permite agregar dos veces el mismo accesorio a una renta', function () {
    $accesorio = Accesorio::factory()->create([
        'precio_unitario' => 1000
    ]);

    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    $renta->accesorios()->attach(
        $accesorio->id,
        [
            'cantidad' => 1,
            'precio_diario' => 1000,
            'subtotal' => 1000,
        ]
    );

    expect(
        fn () => app(AccesorioService::class)
            ->agregarARenta(
                $accesorio,
                $renta,
                1
            )
    )->toThrow(
        AccesorioException::class
    );
});


it('congela el precio calcula subtotal y actualiza la renta dentro de la operacion', function () {
    $accesorio = Accesorio::factory()->create([
        'precio_unitario' => 1500
    ]);

    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    app(AccesorioService::class)
        ->agregarARenta(
            $accesorio,
            $renta,
            2
        );

    $pivot = DB::table(
        'accesorio_renta'
    )->first();

    expect(
        (float) $pivot->precio_diario
    )->toBe(1500.0);

    expect(
        (float) $pivot->subtotal
    )->toBe(3000.0);

    expect(
        (float) $renta
            ->fresh()
            ->monto_total
    )->toBe(13000.0);
});


it('revierte la transaccion si ocurre un fallo despues de insertar la pivote', function () {
    $accesorio = Accesorio::factory()->create([
        'precio_unitario' => 1500
    ]);

    $renta = Renta::factory()->create([
        'fecha_fin' => now()->addDay(),
        'monto_total' => 10000,
    ]);

    Renta::updating(function () {
        throw new RuntimeException(
            'Fallo simulado durante la actualizacion de la renta.'
        );
    });

    expect(
        fn () => app(AccesorioService::class)
            ->agregarARenta(
                $accesorio,
                $renta,
                2
            )
    )->toThrow(
        RuntimeException::class
    );

    expect(
        DB::table('accesorio_renta')
            ->count()
    )->toBe(0);

    expect(
        (float) $renta
            ->fresh()
            ->monto_total
    )->toBe(10000.0);

    Renta::flushEventListeners();
});


it('rechaza una invocacion directa para crear un accesorio con un rol no autorizado', function () {
    $usuario = User::create([
        'name' => 'Gestor Accesorio Test',
        'email' => 'gestor_accesorio_' . uniqid() . '@correo.com',
        'password' => 'Password123',
    ]);

    $role = Role::firstOrCreate([
        'name' => 'Gestor_Rentas',
        'guard_name' => 'web',
    ]);

    $usuario->assignRole($role);

    $this->actingAs($usuario);

    expect(
        fn () => app(AccesorioService::class)
            ->crear([
                'nombre' => 'Accesorio No Autorizado',
                'precio_unitario' => 1000,
            ])
    )->toThrow(
        AuthorizationException::class
    );

    $this->assertDatabaseMissing(
        'accesorios',
        [
            'nombre' => 'Accesorio No Autorizado',
        ]
    );
});


function crearAccesorioServiceConDoble(): array
{
    $accesorioModel = Mockery::mock(
        Accesorio::class
    );

    $service = new AccesorioService(
        $accesorioModel
    );

    return [
        'accesorioModel' => $accesorioModel,
        'service' => $service,
    ];
}


function simularUsuarioAutorizadoAccesorio(): User
{
    $usuario = Mockery::mock(
        User::class
    );

    $usuario
        ->shouldReceive('hasAnyRole')
        ->once()
        ->andReturn(true);

    Auth::shouldReceive('user')
        ->once()
        ->andReturn($usuario);

    return $usuario;
}


it('crea un accesorio utilizando un doble del modelo', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $accesorioModel =
        $dependencias['accesorioModel'];

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $query = Mockery::mock();

    $accesorioCreado = Mockery::mock(
        Accesorio::class
    );

    $data = [
        'nombre' => 'GPS',
        'precio_unitario' => 2500,
    ];

    $accesorioModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('create')
        ->once()
        ->with($data)
        ->andReturn($accesorioCreado);

    $resultado =
        $service->crear($data);

    expect($resultado)
        ->toBe($accesorioCreado);
});


it('actualiza un accesorio utilizando un doble', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = Mockery::mock(
        Accesorio::class
    );

    $data = [
        'nombre' => 'GPS Premium',
        'precio_unitario' => 3000,
    ];

    $accesorio
        ->shouldReceive('update')
        ->once()
        ->with($data)
        ->andReturn(true);

    $resultado =
        $service->actualizar(
            $accesorio,
            $data
        );

    expect($resultado)
        ->toBe($accesorio);
});


it('elimina un accesorio sin rentas utilizando dobles', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = Mockery::mock(
        Accesorio::class
    );

    $relacion = Mockery::mock();

    $accesorio
        ->shouldReceive('rentas')
        ->once()
        ->andReturn($relacion);

    $relacion
        ->shouldReceive('exists')
        ->once()
        ->andReturn(false);

    $accesorio
        ->shouldReceive('delete')
        ->once()
        ->andReturn(true);

    $service->eliminar($accesorio);
});


it('rechaza eliminar un accesorio con rentas utilizando dobles', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = Mockery::mock(
        Accesorio::class
    );

    $relacion = Mockery::mock();

    $accesorio
        ->shouldReceive('rentas')
        ->once()
        ->andReturn($relacion);

    $relacion
        ->shouldReceive('exists')
        ->once()
        ->andReturn(true);

    $accesorio
        ->shouldNotReceive('delete');

    expect(
        fn () => $service
            ->eliminar($accesorio)
    )->toThrow(
        AccesorioException::class,
        'No se puede eliminar el accesorio porque tiene rentas asociadas.'
    );
});


it('rechaza una renta finalizada usando un doble de Renta', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = new Accesorio();

    $renta = new Renta();

    $renta->fecha_fin =
        now()->subDay();

    $renta->monto_total = 10000;

    expect(
        fn () => $service
            ->agregarARenta(
                $accesorio,
                $renta,
                1
            )
    )->toThrow(
        AccesorioException::class,
        'No se puede agregar un accesorio a una renta finalizada.'
    );
});


it('rechaza un accesorio duplicado utilizando dobles', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = Mockery::mock(
        Accesorio::class
    )->makePartial();

    $accesorio->id = 5;
    $accesorio->precio_unitario = 1000;

    $renta = Mockery::mock(
        Renta::class
    )->makePartial();

    $renta->fecha_fin = now()->addDay();
    $renta->monto_total = 10000;

    $relacion = Mockery::mock();

    $renta
        ->shouldReceive('accesorios')
        ->once()
        ->andReturn($relacion);

    $relacion
        ->shouldReceive('where')
        ->once()
        ->with(
            'accesorios.id',
            5
        )
        ->andReturnSelf();

    $relacion
        ->shouldReceive('exists')
        ->once()
        ->andReturn(true);

    expect(
        fn () => $service
            ->agregarARenta(
                $accesorio,
                $renta,
                1
            )
    )->toThrow(
        AccesorioException::class,
        'El accesorio ya está asociado a esta renta.'
    );
});


it('agrega un accesorio a una renta utilizando dobles', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $accesorio = Mockery::mock(
        Accesorio::class
    )->makePartial();

    $accesorio->id = 10;
    $accesorio->precio_unitario = 1500;

    $renta = Mockery::mock(
        Renta::class
    )->makePartial();

    $renta->fecha_fin =
        now()->addDay();

    $renta->monto_total = 10000;

    $relacion = Mockery::mock();

    $renta
        ->shouldReceive('accesorios')
        ->twice()
        ->andReturn($relacion);

    $relacion
        ->shouldReceive('where')
        ->once()
        ->with(
            'accesorios.id',
            10
        )
        ->andReturnSelf();

    $relacion
        ->shouldReceive('exists')
        ->once()
        ->andReturn(false);

    $relacion
        ->shouldReceive('attach')
        ->once()
        ->with(
            10,
            [
                'cantidad' => 2,
                'precio_diario' => 1500.0,
                'subtotal' => 3000.0,
            ]
        );

    $renta
        ->shouldReceive('save')
        ->once()
        ->andReturn(true);

    $renta
        ->shouldReceive('load')
        ->once()
        ->with('accesorios')
        ->andReturnSelf();

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(
            function ($callback) {
                return $callback();
            }
        );

    $resultado =
        $service->agregarARenta(
            $accesorio,
            $renta,
            2
        );

    expect($resultado)
        ->toBe($renta);

    expect(
        (float) $renta->monto_total
    )->toBe(13000.0);
});


it('rechaza mediante doble un usuario sin permisos para crear accesorios', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $service =
        $dependencias['service'];

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
            'nombre' => 'GPS',
            'precio_unitario' => 1000,
        ])
    )->toThrow(
        AuthorizationException::class
    );
});


it('limita per page a 25 utilizando un doble de consulta', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $accesorioModel =
        $dependencias['accesorioModel'];

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $query = Mockery::mock();
    $paginator = Mockery::mock();

    $accesorioModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('when')
        ->times(3)
        ->andReturnSelf();

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
        ->with(25)
        ->andReturn($paginator);

    $resultado =
        $service->listar([
            'per_page' => 100
        ]);

    expect($resultado)
        ->toBe($paginator);
});


it('corrige sort y direction invalidos al listar accesorios', function () {
    $dependencias = crearAccesorioServiceConDoble();

    $accesorioModel =
        $dependencias['accesorioModel'];

    $service =
        $dependencias['service'];

    simularUsuarioAutorizadoAccesorio();

    $query = Mockery::mock();
    $paginator = Mockery::mock();

    $accesorioModel
        ->shouldReceive('newQuery')
        ->once()
        ->andReturn($query);

    $query
        ->shouldReceive('when')
        ->times(3)
        ->andReturnSelf();

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

    $resultado =
        $service->listar([
            'sort' => 'campo_falso',
            'direction' => 'lado'
        ]);

    expect($resultado)
        ->toBe($paginator);
});