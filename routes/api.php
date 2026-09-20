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

// ruta publica
Route::post('login', LoginController::class);

Route::middleware('auth:sanctum')->get('user', function (Request $request) {
    return new UserResource($request->user());
});

// Estados routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('estados', EstadoController::class);
});

    // definicion de recurso
    Route::apiResource('rentas', RentaController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::apiResource('estados', EstadoController::class);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('accesorios', AccesorioController::class);

    // accion extra
    Route::post(
        'rentas/{renta}/accesorios/{accesorio}',
        [AccesorioController::class, 'agregarARenta']
    );
});