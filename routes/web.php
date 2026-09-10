<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\VehiculosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas CRUD API
Route::resource('vehiculos', VehiculosController::class);
Route::resource('accesorios', AccesorioController::class);
Route::resource('rentas', RentaController::class);
Route::resource('estados', EstadoController::class);
Route::resource('categorias', CategoriaController::class);
Route::apiResource('clientes', ClienteController::class);

// Rutas de reportes y consultas
Route::prefix('reportes')->group(function () {
    Route::get('/scopes', [ReporteController::class, 'obtenerRentasVigentesPorMonto']);
    Route::get('/group-by', [ReporteController::class, 'obtenerIngresosYTotalesPorVehiculo']);
    Route::get('/pivote', [ReporteController::class, 'obtenerEstadisticasAccesoriosRenta']);
});