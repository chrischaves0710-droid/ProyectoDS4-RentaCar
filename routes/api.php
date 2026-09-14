<?php

use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('vehiculos', VehiculoController::class);
Route::apiResource('estados', EstadoController::class);
Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('clientes', ClienteController::class);