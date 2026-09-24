<?php

use App\Models\Estado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Crea un usuario de prueba y le asigna el rol indicado.
 */
function crearUsuarioEstadoApi(string $rol): User
{
    $usuario = User::create([
        'name' => 'Usuario Estado API ' . $rol,
        'email' => strtolower($rol) . '_estado_' . uniqid() . '@correo.com',
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
| Admin_General tiene permitido acceder a GET /api/estados.
| Se verifica:
| - código HTTP 200
| - estructura JSON
| - presencia del estado creado
*/

it('Admin_General puede listar estados y recibe la estructura correcta', function () {

    $estado = Estado::factory()->create([
        'nombre' => 'Disponible API',
    ]);

    $usuario = crearUsuarioEstadoApi(
        'Admin_General'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->getJson(
        '/api/estados'
    );

    $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'created_at',
                    'updated_at',
                ],
            ],

            'meta' => [
                'current_page',
                'total_pages',
                'total_records',
            ],

            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);

    $response->assertJsonFragment([
        'id' => $estado->id,
        'nombre' => 'Disponible API',
    ]);
});


/*
|--------------------------------------------------------------------------
| PRUEBA 2
|--------------------------------------------------------------------------
| Admin_General puede crear un Estado.
| Se verifica:
| - código HTTP 201
| - estructura JSON
| - encabezado Location
| - registro guardado en BD
*/

it('Admin_General puede crear un estado y recibe 201 con Location', function () {

    $usuario = crearUsuarioEstadoApi(
        'Admin_General'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->postJson(
        '/api/estados',
        [
            'nombre' => 'Reservado API',
        ]
    );

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'created_at',
                'updated_at',
            ],
        ]);

    $response->assertJsonPath(
        'data.nombre',
        'Reservado API'
    );

    $estadoId = $response->json(
        'data.id'
    );

    $response->assertHeader(
        'Location',
        route(
            'estados.show',
            [
                'estado' => $estadoId,
            ]
        )
    );

    $this->assertDatabaseHas(
        'estados',
        [
            'id' => $estadoId,
            'nombre' => 'Reservado API',
        ]
    );
});


/*
|--------------------------------------------------------------------------
| PRUEBA 3
|--------------------------------------------------------------------------
| Gestor_Rentas NO está autorizado para Estados.
| routes/api.php permite únicamente:
| - Admin_General
| - Admin_Inventarios
|
| Se verifica:
| - código HTTP 403
*/

it('Gestor_Rentas no puede acceder a los endpoints de estados', function () {

    $usuario = crearUsuarioEstadoApi(
        'Gestor_Rentas'
    );

    Sanctum::actingAs(
        $usuario,
        ['*']
    );

    $response = $this->getJson(
        '/api/estados'
    );

    $response->assertStatus(403);
});