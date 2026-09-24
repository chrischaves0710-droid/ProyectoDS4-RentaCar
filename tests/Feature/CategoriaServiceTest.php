<?php

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Renta;
use App\Services\CategoriaService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Doble de prueba para Auth/User sin requerir UserFactory
    /** @var User|\Mockery::MockInterface $userMock */
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->shouldReceive('hasAnyRole')->andReturn(true);

    Auth::shouldReceive('user')->andReturn($userMock);
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
});

afterEach(function () {
    Mockery::close();
});

// ==========================================
// 1. CAMINO FELIZ (4 PRUEBAS)
// ==========================================

it('1. [Camino Feliz] listarConFiltros: retorna la lista paginada procesada correctamente', function () {
    for ($i = 1; $i <= 15; $i++) {
        Categoria::create(['nombre' => "Categoria $i"]);
    }

    $service = new CategoriaService();
    $resultado = $service->listarConFiltros(perPage: 10);

    expect($resultado->items())->toHaveCount(10);
});

it('2. [Camino Feliz] crear: registra exitosamente una categoria cuando no existe duplicada', function () {
    $service = new CategoriaService();
    $categoria = $service->crear(['nombre' => 'SUV Premium']);

    expect($categoria)->toBeInstanceOf(Categoria::class)
        ->and($categoria->nombre)->toBe('SUV Premium');

    $this->assertDatabaseHas('categorias', ['nombre' => 'SUV Premium']);
});

it('3. [Camino Feliz] actualizar: modifica la categoria usando un doble de prueba cuando no hay rentas activas', function () {
    /** @var Categoria|\Mockery::MockInterface $categoriaMock */
    $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
    $categoriaMock->id = 999;
    $categoriaMock->nombre = 'Sedan Original';

    // Mock del método update de Eloquent
    $categoriaMock->shouldReceive('update')
        ->once()
        ->with(['nombre' => 'Sedan Actualizado'])
        ->andReturn(true);

    $service = new CategoriaService();
    /** @var Categoria $categoriaMock */
    $resultado = $service->actualizar($categoriaMock, ['nombre' => 'Sedan Actualizado']);

    expect($resultado)->toBe($categoriaMock);
});

it('4. [Camino Feliz] eliminar: elimina la categoria usando un doble de prueba si cumple las condiciones', function () {
    /** @var Categoria|\Mockery::MockInterface $categoriaMock */
    $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
    $categoriaMock->id = 10;

    // Stub de la relación vehiculos mockeando la clase HasMany explícitamente
    $relationMock = Mockery::mock(HasMany::class);
    $relationMock->shouldReceive('exists')->andReturn(false);
    $categoriaMock->shouldReceive('vehiculos')->andReturn($relationMock);

    $categoriaMock->shouldReceive('delete')->once()->andReturn(true);

    $service = new CategoriaService();
    /** @var Categoria $categoriaMock */
    $resultado = $service->eliminar($categoriaMock);

    expect($resultado)->toBeTrue();
});

// ==========================================
// 2. VIOLACIÓN DE REGLAS DE NEGOCIO (4 PRUEBAS)
// ==========================================

it('5. [Regla 1] crear: lanza excepcion al intentar registrar un nombre duplicado (case-insensitive)', function () {
    Categoria::create(['nombre' => 'SUV']);

    $service = new CategoriaService();

    expect(fn () => $service->crear(['nombre' => 'suv']))
        ->toThrow(CategoriaException::class, 'Ya existe una categoría registrada con ese nombre.');
});

it('6. [Regla 2] actualizar: lanza excepcion si la categoria tiene vehiculos en rentas activas', function () {
    $categoria = Categoria::create(['nombre' => 'Deportivos']);
    $vehiculo = Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

    Renta::factory()->create([
        'vehiculo_id' => $vehiculo->id,
        'fecha_fin' => now()->addDays(5),
    ]);

    $service = new CategoriaService();

    expect(fn () => $service->actualizar($categoria, ['nombre' => 'Nuevo Nombre']))
        ->toThrow(CategoriaException::class, 'No se puede modificar la categoría porque tiene vehículos en rentas activas.');
});

it('7. [Regla 3] eliminar: lanza excepcion si el doble de prueba indica que tiene vehiculos asociados', function () {
    /** @var Categoria|\Mockery::MockInterface $categoriaMock */
    $categoriaMock = Mockery::mock(Categoria::class)->makePartial();
    $categoriaMock->id = 8;

    // Stub de la relación vehiculos mockeando la clase HasMany explícitamente
    $relationMock = Mockery::mock(HasMany::class);
    $relationMock->shouldReceive('exists')->andReturn(true);
    $categoriaMock->shouldReceive('vehiculos')->andReturn($relationMock);

    $service = new CategoriaService();
    /** @var Categoria $categoriaMock */
    expect(fn () => $service->eliminar($categoriaMock))
        ->toThrow(CategoriaException::class, 'No se puede eliminar la categoría porque tiene vehículos asociados.');
});

it('8. [Regla 4] eliminar: lanza excepcion al intentar eliminar la categoria protegida ID 1', function () {
    /** @var Categoria|\Mockery::MockInterface $categoriaProtegidaMock */
    $categoriaProtegidaMock = Mockery::mock(Categoria::class)->makePartial();
    $categoriaProtegidaMock->id = 1;

    $service = new CategoriaService();
    /** @var Categoria $categoriaProtegidaMock */
    expect(fn () => $service->eliminar($categoriaProtegidaMock))
        ->toThrow(CategoriaException::class, 'No se puede eliminar la categoría principal del sistema.');
});

// ==========================================
// 3. CASOS LÍMITE (4 PRUEBAS)
// ==========================================

it('9. [Caso Límite 1] listarConFiltros: sanea perPage negativo (-10) ajustandolo al minimo de 1', function () {
    Categoria::create(['nombre' => 'Cat Límite 1']);

    $service = new CategoriaService();
    $resultado = $service->listarConFiltros(perPage: -10);

    expect($resultado->perPage())->toBe(1);
});

it('10. [Caso Límite 2] listarConFiltros: acota perPage excesivo (100) al limite maximo de 50', function () {
    Categoria::create(['nombre' => 'Cat Límite 2']);

    $service = new CategoriaService();
    $resultado = $service->listarConFiltros(perPage: 100);

    expect($resultado->perPage())->toBe(50);
});

it('11. [Caso Límite 3] listarConFiltros: realiza fallback seguro a created_at ante un sortBy invalido', function () {
    Categoria::create(['nombre' => 'Cat Límite 3']);

    $service = new CategoriaService();
    $resultado = $service->listarConFiltros(sortBy: 'columna_inexistente_sql');

    expect($resultado)->not()->toBeNull();
});

it('12. [Caso Límite 4] crear: remueve espacios laterales (trim) en el nombre antes de procesar', function () {
    $service = new CategoriaService();
    $categoria = $service->crear(['nombre' => '   Deportivo Trim   ']);

    expect($categoria->nombre)->toBe('Deportivo Trim');
});