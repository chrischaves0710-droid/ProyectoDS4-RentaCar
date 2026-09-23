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

// Rutas públicas
Route::post('/login', LoginController::class)->middleware('throttle:5,1');
// Route::post('/register', [AuthController::class, 'register']); // Descomenta si tienes tu ruta de registro aquí

// Protección general con token para TODO el sistema
Route::middleware('auth:sanctum')->group(function () {
    
    // Cierre de sesión
    Route::post('/logout', LogoutController::class);

    Route::get('user', function (Request $request) {
        return new UserResource($request->user());
    });

    // scceso Exclusivo: Admin_General y Admin_Inventarios
    // se usa el middleware 'role' de Spatie 
    Route::apiResource('estados', EstadoController::class)
        ->middleware('role:Admin_General|Admin_Inventarios');
        
    Route::apiResource('categorias', CategoriaController::class)
        ->middleware('role:Admin_General|Admin_Inventarios');

    // acceso misxto, ectura para Gestor, CRUD completo para Inventarios y General
    Route::apiResource('vehiculos', VehiculoController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios|Gestor_Rentas');
        
    Route::apiResource('vehiculos', VehiculoController::class)
        ->except(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios');

    Route::apiResource('accesorios', AccesorioController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios|Gestor_Rentas');
        
    Route::apiResource('accesorios', AccesorioController::class)
        ->except(['index', 'show'])
        ->middleware('role:Admin_General|Admin_Inventarios');

    // Clientes y Rentas, Interviene el Cliente final
    // se el paso a los roles administrativos y al Cliente. La Policy se encargará de limitar las acciones del Cliente a sus propios recursos
    Route::apiResource('clientes', ClienteController::class)
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');
        
    Route::apiResource('rentas', RentaController::class)
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');

    // Acción extra
    Route::post('rentas/{renta}/accesorios/{accesorio}', [AccesorioController::class, 'agregarARenta'])
        ->middleware('role:Admin_General|Gestor_Rentas');
});