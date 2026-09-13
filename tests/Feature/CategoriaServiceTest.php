<?php

use App\Exceptions\CategoriaException;
use App\Models\Categoria;
use App\Services\CategoriaService;

it('tira una excepcion si se intenta crear una categoria duplicada y no la guarda en la base de datos', function () {
    // 1. Crear la categoría inicial
    Categoria::factory()->create([
        'nombre' => 'SUV',
    ]);

    // 2. Verificar que se lance la excepción al intentar crear otra con el mismo nombre (insensible a mayúsculas)
    expect(fn () => app(CategoriaService::class)->crear(['nombre' => 'suv']))
        ->toThrow(CategoriaException::class);

    // 3. Confirmar que en la base de datos sigue existiendo solo 1 registro con ese nombre
    expect(Categoria::whereRaw('LOWER(nombre) = ?', ['suv'])->count())->toBe(1);
});

it('al crear una categoria el total de categorias en la base de datos aumenta en 1', function () {
    // 1. Estado inicial
    $totalAntes = Categoria::count();

    // 2. Ejecutar la acción del servicio
    app(CategoriaService::class)->crear([
        'nombre' => 'Compacto',
    ]);

    // 3. Verificar el cambio en el conteo total de la base de datos
    $totalDespues = Categoria::count();

    expect($totalDespues)->toBe($totalAntes + 1);
});

it('tira una excepcion si se intenta eliminar la categoria protegida del sistema', function () {
    // 1. Crear la categoría base protegida (ID 1)
    $categoriaProtegida = Categoria::factory()->create([
        'id' => 1,
        'nombre' => 'General',
    ]);

    // 2. Verificar la excepción esperada
    expect(fn () => app(CategoriaService::class)->eliminar($categoriaProtegida))
        ->toThrow(CategoriaException::class);

    // 3. Confirmar que la categoría no fue eliminada de la base de datos
    expect(Categoria::find(1))->not->toBeNull();
});