<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VehiculosController;

Route::get('/', function () {
    return view('welcome');
});

// ruta para el CRUD de vehiculos
Route::resource('vehiculos', VehiculosController::class);

use App\Http\Controllers\Api\AccesorioController;

// Rutas API para Accesorios
Route::get('/accesorios', [AccesorioController::class, 'index']);
Route::get('/accesorios/{id}', [AccesorioController::class, 'show']);

use App\Http\Controllers\Api\RentaController;

// Rutas API para Rentas
Route::get('/rentas', [RentaController::class, 'index']);
Route::get('/rentas/{id}', [RentaController::class, 'show']);