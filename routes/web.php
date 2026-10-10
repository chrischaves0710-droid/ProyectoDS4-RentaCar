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

// ==========================================
// Vistas del Gerente General
// ==========================================
Route::view('/lab8/gestion-clientes', 'lab8.Vistas_Gerente_General.gestion-clientes')
    ->name('lab8.gestion-clientes');

Route::view('/lab8/registrar-cliente', 'lab8.Vistas_Gerente_General.registrar-cliente')
    ->name('lab8.registrar-cliente');

Route::view('/lab8/editar-cliente', 'lab8.Vistas_Gerente_General.editar-cliente')
    ->name('lab8.editar-cliente');

// ==========================================
// Vistas del Admin de Inventario
// ==========================================
Route::view('/lab8/gestion-vehiculos', 'lab8.Vistas_Admin_Inventario.gestion-vehiculos')
    ->name('lab8.gestion-vehiculos');

// ==========================================
// Vistas del Cliente
// ==========================================
Route::view('/lab8/catalogo', 'lab8.Vistas_Cliente.catalogo')
    ->name('lab8.catalogo');

// ==========================================
// Vistas del Gestor de Rentas
// ==========================================
Route::view('/lab8/nueva-renta', 'lab8.Vistas_Gestor_Rentas.nueva-renta')
    ->name('lab8.nueva-renta');

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