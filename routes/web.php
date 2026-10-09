<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/lab8/nueva-renta', 'lab8.nueva-renta')
    ->name('lab8.nueva-renta');

Route::view('/lab8/catalogo', 'lab8.catalogo')
    ->name('lab8.catalogo');

Route::view('/lab8/gestion-vehiculos', 'lab8.gestion-vehiculos')
    ->name('lab8.gestion-vehiculos');

// Documentación Swagger UI en el navegador
Route::view('/docs', 'docs.index');

// Entrega del archivo físico OpenAPI YAML
Route::get('/docs/openapi.yaml', function () {
    return response()->file(
        base_path('docs/openapi.yaml'),
        ['Content-Type' => 'application/yaml']
    );
});

// Rutas CRUD API
Route::resource('vehiculos', VehiculoController::class);
Route::resource('accesorios', AccesorioController::class);
Route::resource('rentas', RentaController::class);
Route::resource('estados', EstadoController::class);
Route::resource('categorias', CategoriaController::class);


// Rutas de reportes y consultas
Route::prefix('reportes')->group(function () {
    Route::get('/scopes', [ReporteController::class, 'obtenerRentasVigentesPorMonto']);
    Route::get('/group-by', [ReporteController::class, 'obtenerIngresosYTotalesPorVehiculo']);
    Route::get('/pivote', [ReporteController::class, 'obtenerEstadisticasAccesoriosRenta']);
});