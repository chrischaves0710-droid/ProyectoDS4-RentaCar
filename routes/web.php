<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehiculosController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\AccesorioController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para el CRUD de vehiculos
Route::resource('vehiculos', VehiculosController::class);

// Rutas para el CRUD de Accesorios
Route::resource('accesorios', AccesorioController::class);

// Rutas para el CRUD de Rentas
Route::resource('rentas', RentaController::class);
