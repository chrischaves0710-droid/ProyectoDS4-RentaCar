<?php

use App\Models\Accesorio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Crea un usuario de prueba y le asigna
 * el rol solicitado.
 */
function crearUsuarioAccesorioApi(string $rol): User
{
    $usuario = User::create([
        'name' => 'Usuario Accesorio API ' . $rol,
        'email' => strtolower($rol) . '_accesorio_' . uniqid() . '@correo.com',
        'password' => 'Password123!',
    ]);

    $role = Role::firstOrCreate([
        'name' => $rol,
        'guard_name' => 'web',
    ]);

    $usuario->assignRole($role);

    return $usuario;
}


/*
|--------------------------------------------------------------------------
| PRUEBA 1
|--------------------------------------------------------------------------
| Gestor_Rentas puede consultar accesorios.
|
| Según routes/api.php puede ejecutar:
| GET /api/accesorios
| GET /api/accesorios/{id}
|
| Se verifica:
| - código HTTP 200
| - estructura JSON paginada
| - datos del accesorio
*/

it('Gestor_Rentas puede listar accesorios y recibe la estructura correcta', function () {

    $accesorio = Accesorio::factory()->create([
        'nombre' => 'GPS API',
        'precio_unitario' => 1500,
    ]);

    $usuario = crearUsuarioAccesorioApi(
        'Gestor_Rentas'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->getJson(
        '/api/accesorios'
    );

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'precio_unitario',
                    'created_at',
                    'updated_at',
                ],
            ],

            'links',

            'meta',
        ]);

    $response->assertJsonFragment([
        'id' => $accesorio->id,
        'nombre' => 'GPS API',
    ]);
});


/*
|--------------------------------------------------------------------------
| PRUEBA 2
|--------------------------------------------------------------------------
| Admin_Inventarios puede crear Accesorios.
|
| Se verifica:
| - código HTTP 201
| - estructura JSON
| - Location
| - persistencia en BD
*/

it('Admin_Inventarios puede crear un accesorio y recibe 201', function () {

    $usuario = crearUsuarioAccesorioApi(
        'Admin_Inventarios'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->postJson(
        '/api/accesorios',
        [
            'nombre' => 'Silla Infantil API',
            'precio_unitario' => 2500,
        ]
    );

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'precio_unitario',
                'created_at',
                'updated_at',
            ],
        ]);

    $response->assertJsonPath(
        'data.nombre',
        'Silla Infantil API'
    );

    $accesorioId = $response->json(
        'data.id'
    );

    $response->assertHeader(
        'Location',
        route(
            'accesorios.show',
            [
                'accesorio' => $accesorioId,
            ]
        )
    );

    $this->assertDatabaseHas(
        'accesorios',
        [
            'id' => $accesorioId,
            'nombre' => 'Silla Infantil API',
            'precio_unitario' => 2500,
        ]
    );
});


/*
|--------------------------------------------------------------------------
| PRUEBA 3
|--------------------------------------------------------------------------
| Gestor_Rentas puede leer accesorios,
| pero NO puede crearlos.
|
| Se verifica:
| - código HTTP 403
| - no se inserta el registro
*/

it('Gestor_Rentas no puede crear accesorios', function () {

    $usuario = crearUsuarioAccesorioApi(
        'Gestor_Rentas'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->postJson(
        '/api/accesorios',
        [
            'nombre' => 'Accesorio Prohibido',
            'precio_unitario' => 1000,
        ]
    );

    $response->assertStatus(403);

    $this->assertDatabaseMissing(
        'accesorios',
        [
            'nombre' => 'Accesorio Prohibido',
        ]
    );
});