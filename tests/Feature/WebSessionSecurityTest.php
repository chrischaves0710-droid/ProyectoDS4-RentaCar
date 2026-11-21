<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

// Pruebas aisladas: phpunit.xml usa SQLite :memory: y RefreshDatabase.
function crearUsuarioParaWebSecurityTest(string $nombreRol): User
{
    $rol = Role::firstOrCreate([
        'name' => $nombreRol,
        'guard_name' => 'web',
    ]);

    $usuario = User::factory()->create([
        'password' => Hash::make('ClavePrueba2026!'),
    ]);

    $usuario->assignRole($rol);

    return $usuario;
}

beforeEach(function () {
    $this->withoutVite();
});

it('impide abrir el panel de gerente sin una sesion web', function () {
    $this->get('/lab8/gerente/vehiculos')
        ->assertRedirect(route('login'));
});

it('impide usar rutas CRUD web sin iniciar sesion', function () {
    $this->get('/vehiculos')
        ->assertRedirect(route('login'));
});

it('impide que un cliente abra las paginas del gerente', function () {
    $cliente = crearUsuarioParaWebSecurityTest('Cliente');

    $this->actingAs($cliente, 'web')
        ->get('/lab8/gerente/vehiculos')
        ->assertForbidden();
});

it('permite al gerente autenticado abrir su pagina', function () {
    $gerente = crearUsuarioParaWebSecurityTest('Admin_General');

    $this->actingAs($gerente, 'web')
        ->get('/lab8/gerente/vehiculos')
        ->assertOk();
});

it('inicia sesion web y entrega un token de Sanctum', function () {
    $gerente = crearUsuarioParaWebSecurityTest('Admin_General');

    $respuesta = $this->postJson('/web/login', [
        'email' => $gerente->email,
        'password' => 'ClavePrueba2026!',
    ]);

    $respuesta->assertOk()->assertJsonStructure(['token', 'roles']);

    $this->assertAuthenticatedAs($gerente, 'web');
    expect($respuesta->json('roles'))->toContain('Admin_General');
    expect(PersonalAccessToken::count())->toBe(1);
});

it('revoca el token y cierra la sesion web al salir', function () {
    $gerente = crearUsuarioParaWebSecurityTest('Admin_General');

    $respuesta = $this->postJson('/web/login', [
        'email' => $gerente->email,
        'password' => 'ClavePrueba2026!',
    ]);

    $respuesta->assertOk();
    $token = $respuesta->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/web/logout')
        ->assertOk();

    $this->assertGuest('web');
    expect(PersonalAccessToken::count())->toBe(0);
});
