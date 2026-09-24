<?php

use App\Models\Categoria;
use App\Models\Estado; 
use App\Models\User;
use App\Models\Vehiculo;
use Database\Seeders\EstadoSeeder;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // se prepara la base de datos temporal
    $this->seed(EstadoSeeder::class);

    Role::firstOrCreate(['name' => 'Admin_General', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin_Inventarios', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Cliente', 'guard_name' => 'web']);
});

// ==========================================
// PRUEBAS DE ESTRUCTURA Y CÓDIGOS DE ESTADO
// ==========================================

it('API 1: Devuelve código 200 y una estructura JSON válida al listar vehículos', function () {
    // simulacion de un login usando un Token de Sanctum
    $admin = User::factory()->create();
    $admin->assignRole('Admin_General');
    Sanctum::actingAs($admin, ['*']);

    //se crean datos falsos
    $categoria = Categoria::factory()->create();
    Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

    // petición real HTTP GET
    $response = $this->getJson('/api/vehiculos');

    // se prueba el código 200 HTTP
    $response->assertStatus(200);
    
    // la respuesta debe tener una estructura JSON válida
    expect($response->json())->toBeArray();
});

it('API 2: Devuelve código 422 y la estructura de errores de validación si los datos son malos', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin_General');
    Sanctum::actingAs($admin, ['*']);

    // se manda un POST vacío, lo que debería disparar las validaciones del Request
    $response = $this->postJson('/api/vehiculos', []);

    // Verificamos el error 422 (Unprocessable Entity) y que diga qué campos fallaron
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['placa', 'marca', 'modelo', 'precio_diario', 'categoria_id']);
});

it('API 3: Devuelve 404 Not Found al solicitar un vehículo que no existe', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin_General');
    Sanctum::actingAs($admin, ['*']);

    // se intenta ver el vehículo número 99,999 (que no existe)
    $response = $this->getJson('/api/vehiculos/99999');

    $response->assertStatus(404);
});

// ==========================================
// PRUEBAS DE ACCESO Y ROLES (MIDDLEWARES)
// ==========================================

it('API 4: Bloquea el acceso y devuelve 401 Unauthorized si no se envía un Token', function () {
    // se hace la petición como alguien anónimo.
    $response = $this->getJson('/api/vehiculos');

    // El sistema debe rebotarlo 
    $response->assertStatus(401);
});

it('API 5: Deniega el acceso y devuelve 403 Forbidden si el rol no tiene permisos', function () {
    //se inicia sesión como Cliente (los clientes no pueden crear vehículos)
    $cliente = User::factory()->create();
    $cliente->assignRole('Cliente');
    Sanctum::actingAs($cliente, ['*']);

    $response = $this->postJson('/api/vehiculos', [
        'placa' => 'HACK-123',
    ]);

    // Verificamos que el sistema responde con un mensaje de prohibicion
    $response->assertStatus(403);
});

it('API 6: Permite el acceso y devuelve 201 Created si el usuario tiene el rol correcto', function () {
    // se inicia sesion como Admin_Inventarios (tiene permiso para crear)
    $adminInventario = User::factory()->create();
    $adminInventario->assignRole('Admin_Inventarios');
    Sanctum::actingAs($adminInventario, ['*']);

    $categoria = Categoria::factory()->create();
    $estado = Estado::firstOrCreate(['nombre' => 'Disponible']);

    $data = [
        'placa' => 'API-777',
        'marca' => 'Honda',
        'modelo' => 'Civic',
        'anno' => 2024,
        'kilometraje' => 0,
        'precio_diario' => 55.00,
        'categoria_id' => $categoria->id,
        'estado_id' => $estado->id,
    ];

    $response = $this->postJson('/api/vehiculos', $data);

    // se verificaque se creó correctamente
    $response->assertStatus(201);
    
    // se comprueba que la base de datos realmente guardó el registro
    $this->assertDatabaseHas('vehiculos', ['placa' => 'API-777']);
});