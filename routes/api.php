<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;

// Ruta publica
Route::post('login', LoginController::class);

// Proteccion general con token para TODO el sistema
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('user', function (Request $request) {
        return new UserResource($request->user());
    });

    // Definicion de recursos protegidos
    Route::apiResource('rentas', RentaController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::apiResource('estados', EstadoController::class);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('accesorios', AccesorioController::class);

    // Accion extra
    Route::post(
        'rentas/{renta}/accesorios/{accesorio}',
        [AccesorioController::class, 'agregarARenta']
    );
});