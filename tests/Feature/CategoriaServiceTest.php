<?php

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\Vehiculo;
use App\Models\Renta;
use App\Models\User;
use App\Services\CategoriaService;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Models\Role;

/**
 * Función auxiliar para simular que un Administrador está usando el sistema.
 * Usamos inserción directa en lugar de Factory para evitar el error de UserFactory.
 */
function actuarComoAdmin() {
    $admin = User::create([
        'name' => 'Admin Test',
        'email' => 'admin_test_' . uniqid() . '@correo.com',
        'password' => bcrypt('12345678')
    ]);
    
    $rolAdmin = Role::firstOrCreate(['name' => 'Admin_General']);
    $admin->assignRole($rolAdmin);
    test()->actingAs($admin);
    return $admin;
}

// ==========================================
// PRUEBAS DE REGLAS DE NEGOCIO (CRUD)
// ==========================================

it('tira una excepcion si se intenta crear una categoria duplicada y no la guarda en la base de datos', function () {
    actuarComoAdmin();

    Categoria::factory()->create([
        'nombre' => 'SUV',
    ]);

    expect(fn () => app(CategoriaService::class)->crear(['nombre' => 'suv']))
        ->toThrow(CategoriaException::class);

    expect(Categoria::whereRaw('LOWER(nombre) = ?', ['suv'])->count())->toBe(1);
});

it('al crear una categoria el total de categorias en la base de datos aumenta en 1', function () {
    actuarComoAdmin();

    $totalAntes = Categoria::count();

    app(CategoriaService::class)->crear([
        'nombre' => 'Compacto',
    ]);

    $totalDespues = Categoria::count();

    expect($totalDespues)->toBe($totalAntes + 1);
});

it('tira una excepcion si se intenta eliminar la categoria protegida del sistema (Regla 4)', function () {
    actuarComoAdmin();

    $categoriaProtegida = Categoria::find(1) ?? Categoria::factory()->create([
        'id' => 1,
        'nombre' => 'General',
    ]);

    expect(fn () => app(CategoriaService::class)->eliminar($categoriaProtegida))
        ->toThrow(CategoriaException::class);

    expect(Categoria::find(1))->not->toBeNull();
});

it('tira una excepcion si se intenta modificar una categoria con vehiculos en rentas activas (Regla 2)', function () {
    actuarComoAdmin();

    $categoria = Categoria::factory()->create([
        'nombre' => 'Sedan',
    ]);

    $vehiculo = Vehiculo::factory()->create([
        'categoria_id' => $categoria->id,
    ]);

    Renta::factory()->create([
        'vehiculo_id' => $vehiculo->id,
        'fecha_fin' => now()->addDays(5),
    ]);

    expect(fn () => app(CategoriaService::class)->actualizar($categoria, ['nombre' => 'Sedan Modificado']))
        ->toThrow(CategoriaException::class, 'No se puede modificar la categoría porque tiene vehículos en rentas activas.');

    expect($categoria->fresh()->nombre)->toBe('Sedan');
});

// ==========================================
// PRUEBAS DE SEGURIDAD (DEFENSA EN PROFUNDIDAD)
// ==========================================

it('Capa 1: rechaza el acceso a la ruta de categorias si el usuario no tiene el rol correcto', function () {
    $categoria = Categoria::factory()->create();
    
    // Crear un usuario con un rol restringido mediante inserción directa
    $usuarioRestringido = User::create([
        'name' => 'Gestor Test 1',
        'email' => 'gestor_test1_' . uniqid() . '@correo.com',
        'password' => bcrypt('12345678')
    ]);
    $rolGestor = Role::firstOrCreate(['name' => 'Gestor_Rentas']);
    $usuarioRestringido->assignRole($rolGestor);

    // Intentar consumir la ruta usando HTTP
    $response = $this->actingAs($usuarioRestringido)
                     ->putJson("/api/categorias/{$categoria->id}", [
                         'nombre' => 'Intento de Hackeo'
                     ]);

    // Verificar que la ruta protegida por el middleware de Spatie lo bloquea
    $response->assertStatus(403);
});

it('Capa 2: rechaza la invocacion directa al servicio, ignorando la ruta, si no hay rol permitido', function () {
    $categoria = Categoria::factory()->create();
    
    // Crear un usuario con rol restringido mediante inserción directa
    $usuarioRestringido = User::create([
        'name' => 'Gestor Test 2',
        'email' => 'gestor_test2_' . uniqid() . '@correo.com',
        'password' => bcrypt('12345678')
    ]);
    $rolGestor = Role::firstOrCreate(['name' => 'Gestor_Rentas']);
    $usuarioRestringido->assignRole($rolGestor);
    $this->actingAs($usuarioRestringido);

    $service = app(CategoriaService::class);

    // Intentar invocar el servicio directamente saltándose la ruta
    expect(fn () => $service->actualizar($categoria, ['nombre' => 'Hackeo Directo']))
        ->toThrow(AuthorizationException::class, 'No autorizado: Se requieren privilegios de administración.');
        
    // Confirmar que la Capa 2 protegió la base de datos
    expect($categoria->fresh()->nombre)->not->toBe('Hackeo Directo');
});