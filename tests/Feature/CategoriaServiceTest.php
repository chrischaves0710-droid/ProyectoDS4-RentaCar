<?php

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Renta;
use App\Services\CategoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $userMock = Mockery::mock(User::class)->makePartial();
    $userMock->shouldReceive('hasAnyRole')->andReturn(true);

    Auth::shouldReceive('user')->andReturn($userMock);
    Auth::shouldReceive('check')->andReturn(true);
    Auth::shouldReceive('id')->andReturn(1);
});

afterEach(fn () => Mockery::close());

/*
|--------------------------------------------------------------------------
| 1. CAMINO FELIZ
|--------------------------------------------------------------------------
*/

it('1. [Camino Feliz] listarConFiltros: retorna la lista paginada procesada correctamente', function () {
    for ($i = 1; $i <= 15; $i++) {
        Categoria::create(['nombre' => "Categoria $i"]);
    }

    $resultado = (new CategoriaService())->listarConFiltros(perPage: 10);

    expect($resultado->items())->toHaveCount(10);
});

it('2. [Camino Feliz] crear: registra exitosamente una categoria cuando no existe duplicada', function () {
    $categoria = (new CategoriaService())->crear(['nombre' => 'SUV Premium']);

    expect($categoria)->toBeInstanceOf(Categoria::class)
        ->and($categoria->nombre)->toBe('SUV Premium');

    $this->assertDatabaseHas('categorias', ['nombre' => 'SUV Premium']);
});

it('3. [Camino Feliz] actualizar: modifica la categoria correctamente cuando no hay rentas activas', function () {
    $categoria = Categoria::create(['nombre' => 'Sedan Original']);

    $resultado = (new CategoriaService())->actualizar($categoria, ['nombre' => 'Sedan Actualizado']);

    expect($resultado->nombre)->toBe('Sedan Actualizado');
    $this->assertDatabaseHas('categorias', ['nombre' => 'Sedan Actualizado']);
});

it('4. [Camino Feliz] eliminar: elimina la categoria si cumple las condiciones', function () {
    // 1. Ocupamos el ID 1 (protegido por la regla del sistema)
    Categoria::create(['nombre' => 'Categoria Principal ID 1']);

    // 2. Creamos la categoría con ID >= 2 que sí se puede eliminar
    $categoria = Categoria::create(['nombre' => 'Categoria A Eliminar']);

    $resultado = (new CategoriaService())->eliminar($categoria);

    expect($resultado)->toBeTrue();
    $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
});

/*
|--------------------------------------------------------------------------
| 2. VIOLACIÓN DE REGLAS DE NEGOCIO
|--------------------------------------------------------------------------
*/

it('5. [Regla 1] crear: lanza excepcion al intentar registrar un nombre duplicado (case-insensitive)', function () {
    Categoria::create(['nombre' => 'SUV']);

    expect(fn () => (new CategoriaService())->crear(['nombre' => 'suv']))
        ->toThrow(CategoriaException::class, 'Ya existe una categoría registrada con ese nombre.');
});

it('6. [Regla 2] actualizar: lanza excepcion si la categoria tiene vehiculos en rentas activas', function () {
    $categoria = Categoria::create(['nombre' => 'Deportivos']);
    $vehiculo = Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

    Renta::factory()->create([
        'vehiculo_id' => $vehiculo->id,
        'fecha_fin' => now()->addDays(5),
    ]);

    expect(fn () => (new CategoriaService())->actualizar($categoria, ['nombre' => 'Nuevo Nombre']))
        ->toThrow(CategoriaException::class, 'No se puede modificar la categoría porque tiene vehículos en rentas activas.');
});

it('7. [Regla 3] eliminar: lanza excepcion si la categoria tiene vehiculos asociados', function () {
    Categoria::create(['nombre' => 'Categoria Principal ID 1']);
    $categoria = Categoria::create(['nombre' => 'Categoria Con Vehiculos']);
    
    Vehiculo::factory()->create(['categoria_id' => $categoria->id]);

    expect(fn () => (new CategoriaService())->eliminar($categoria))
        ->toThrow(CategoriaException::class, 'No se puede eliminar la categoría porque tiene vehículos asociados.');
});

it('8. [Regla 4] eliminar: lanza excepcion al intentar eliminar la categoria protegida ID 1', function () {
    $categoriaProtegida = Categoria::create(['nombre' => 'Categoria Principal']);

    expect(fn () => (new CategoriaService())->eliminar($categoriaProtegida))
        ->toThrow(CategoriaException::class, 'No se puede eliminar la categoría principal del sistema.');
});

/*
|--------------------------------------------------------------------------
| 3. CASOS LÍMITE
|--------------------------------------------------------------------------
*/

it('9. [Caso Límite 1] listarConFiltros: sanea perPage negativo (-10) ajustandolo al minimo de 1', function () {
    Categoria::create(['nombre' => 'Cat Límite 1']);

    $resultado = (new CategoriaService())->listarConFiltros(perPage: -10);

    expect($resultado->perPage())->toBe(1);
});

it('10. [Caso Límite 2] listarConFiltros: acota perPage excesivo (100) al limite maximo de 50', function () {
    Categoria::create(['nombre' => 'Cat Límite 2']);

    $resultado = (new CategoriaService())->listarConFiltros(perPage: 100);

    expect($resultado->perPage())->toBe(50);
});

it('11. [Caso Límite 3] listarConFiltros: realiza fallback seguro a created_at ante un sortBy invalido', function () {
    Categoria::create(['nombre' => 'Cat Límite 3']);

    $resultado = (new CategoriaService())->listarConFiltros(sortBy: 'columna_inexistente_sql');

    expect($resultado)->not()->toBeNull();
});

it('12. [Caso Límite 4] crear: remueve espacios laterales (trim) en el nombre antes de procesar', function () {
    $categoria = (new CategoriaService())->crear(['nombre' => '   Deportivo Trim   ']);

    expect($categoria->nombre)->toBe('Deportivo Trim');
});