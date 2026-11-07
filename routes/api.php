<?php

use App\Http\Controllers\Api\AccesorioController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\EstadoController;
use App\Http\Controllers\Api\RentaController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\AuthController; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;

Route::post('/login', LoginController::class)->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('catalogos/vehiculos', \App\Http\Controllers\Api\CatalogoVehiculosController::class)
        ->middleware('role:Admin_General|Admin_Inventarios|Gestor_Rentas|Cliente');

    Route::post('/logout', LogoutController::class);

    Route::get('user', function (Request $request) {
        return new UserResource($request->user());
    });

    Route::apiResource('estados', EstadoController::class)
        ->middleware('role:Admin_General|Admin_Inventarios');
        
    Route::apiResource('categorias', CategoriaController::class)
        ->middleware('role:Admin_General|Admin_Inventarios');

    Route::apiResource('vehiculos', VehiculoController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios|Gestor_Rentas|Cliente');
        
    Route::apiResource('vehiculos', VehiculoController::class)
        ->except(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios');

    Route::apiResource('accesorios', AccesorioController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios|Gestor_Rentas|Cliente');
        
    Route::apiResource('accesorios', AccesorioController::class)
        ->except(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios');


    Route::apiResource('clientes', ClienteController::class)
        ->only(['index'])
        ->middleware('role:Admin_General|Gestor_Rentas');

    Route::apiResource('clientes', ClienteController::class)
        ->only(['show'])
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');

    Route::apiResource('clientes', ClienteController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:Admin_General');



    Route::apiResource('rentas', RentaController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');
        Route::patch(
    'rentas/{renta}/finalizar',
    [RentaController::class, 'finalizar']
)->middleware('role:Admin_General|Gestor_Rentas');

    Route::apiResource('rentas', RentaController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:Admin_General|Gestor_Rentas');

    Route::post('rentas/{renta}/accesorios/{accesorio}', [AccesorioController::class, 'agregarARenta'])
        ->middleware('role:Admin_General|Gestor_Rentas');
});
