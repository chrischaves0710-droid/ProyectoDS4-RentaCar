<?php

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('Admin_General');
    Role::findOrCreate('Cliente');
});

/**
 * Función auxiliar para crear un usuario de prueba sin depender de UserFactory
 */
function crearUsuarioTest(string $rol = 'Cliente'): User
{
    $user = User::create([
        'name' => 'Usuario Test',
        'email' => 'test_' . Str::random(8) . '@example.com',
        'password' => bcrypt('password123'),
    ]);

    $user->assignRole($rol);

    return $user;
}

/*
|--------------------------------------------------------------------------
| PRUEBAS DE API DE CATEGORÍA
|--------------------------------------------------------------------------
*/

// PRUEBA 1: GET - 200 OK & Estructura de Respuesta (Acceso Autorizado)
it('1. [GET /api/v1/categorias] Retorna codigo 200, la estructura JSON de paginacion y permite el acceso a usuarios autorizados', function () {
    Categoria::create(['nombre' => 'Categoria Pruebas ' . Str::random(4)]);

    $admin = crearUsuarioTest('Admin_General');
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/categorias');

    if ($response->status() === 404) {
        $response = $this->getJson('/api/categorias');
    }

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nombre']
            ]
        ]);
});

// PRUEBA 2: POST - 201 Created & Estructura de Respuesta
it('2. [POST /api/v1/categorias] Retorna codigo 201 y la estructura JSON de la entidad creada cuando el usuario tiene rol autorizado', function () {
    $admin = crearUsuarioTest('Admin_General');
    Sanctum::actingAs($admin);

    $payload = ['nombre' => 'Crossovers SUV ' . Str::random(4)];

    $response = $this->postJson('/api/v1/categorias', $payload);

    if ($response->status() === 404) {
        $response = $this->postJson('/api/categorias', $payload);
    }

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'nombre']
        ])
        ->assertJson([
            'data' => ['nombre' => $payload['nombre']]
        ]);

    $this->assertDatabaseHas('categorias', ['nombre' => $payload['nombre']]);
});

// PRUEBA 3: POST - 403 Forbidden (Acceso Denegado por Rol)
it('3. [POST /api/v1/categorias] Retorna codigo 403 Forbidden cuando un usuario con rol Cliente intenta crear una categoria', function () {
    $cliente = crearUsuarioTest('Cliente');
    Sanctum::actingAs($cliente);

    $payload = ['nombre' => 'Hatchback Deportivo ' . Str::random(4)];

    $response = $this->postJson('/api/v1/categorias', $payload);

    if ($response->status() === 404) {
        $response = $this->postJson('/api/categorias', $payload);
    }

    $response->assertStatus(403);

    $this->assertDatabaseMissing('categorias', ['nombre' => $payload['nombre']]);
});

// PRUEBA 4: POST - 422 Unprocessable Entity (Validación)
it('4. [POST /api/v1/categorias] Retorna codigo 422 y la estructura JSON con los errores de validacion si el payload es invalido', function () {
    $admin = crearUsuarioTest('Admin_General');
    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/categorias', []);

    if ($response->status() === 404) {
        $response = $this->postJson('/api/categorias', []);
    }

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => ['nombre']
        ]);
});

// PRUEBA 5: PUT - 401 Unauthorized (Sin Autenticación)
it('5. [PUT /api/v1/categorias/{id}] Retorna codigo 401 Unauthorized al intentar modificar un recurso sin token de autenticacion', function () {
    $categoria = Categoria::create(['nombre' => 'Sedan Basico ' . Str::random(4)]);

    $key = $categoria->getKey();

    $response = $this->putJson("/api/v1/categorias/{$key}", [
        'nombre' => 'Sedan Lujo'
    ]);

    if ($response->status() === 404) {
        $response = $this->putJson("/api/categorias/{$key}", [
            'nombre' => 'Sedan Lujo'
        ]);
    }

    $response->assertStatus(401);
});

// PRUEBA 6: DELETE - 200/204 Successful Content (Eliminación Correcta)
it('6. [DELETE /api/v1/categorias/{id}] Retorna codigo exitoso (200/204) al eliminar una categoria si el usuario esta autorizado', function () {
    $admin = crearUsuarioTest('Admin_General');
    Sanctum::actingAs($admin);

    // 1. Ocupar el ID 1 para cumplir la regla de protección del sistema
    Categoria::create(['nombre' => 'Categoria Principal Sistema']);

    // 2. Crear la categoría secundaria (ID >= 2) que sí se eliminará
    $categoriaAEliminar = Categoria::create(['nombre' => 'Pickup Temporal ' . Str::random(4)]);

    $key = $categoriaAEliminar->getKey();

    $response = $this->deleteJson("/api/v1/categorias/{$key}");

    if ($response->status() === 404) {
        $response = $this->deleteJson("/api/categorias/{$key}");
    }

    expect($response->status())->toBeIn([200, 204]);

    $this->assertDatabaseMissing('categorias', [$categoriaAEliminar->getKeyName() => $key]);
});