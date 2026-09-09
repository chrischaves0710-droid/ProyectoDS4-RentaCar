<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\VehiculosController;
use App\Http\Controllers\Api\ClienteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para el CRUD de vehiculos
    Route::resource('vehiculos', VehiculosController::class);

// Rutas para el CRUD de Accesorios
Route::resource('accesorios', AccesorioController::class);

// Rutas para el CRUD de Rentas
Route::resource('rentas', RentaController::class);

// Rutas para el CRUD de Categorías
Route::resource('categorias', CategoriaController::class);

// Rutas para el CRUD de Clientes
Route::apiResource('clientes', ClienteController::class);