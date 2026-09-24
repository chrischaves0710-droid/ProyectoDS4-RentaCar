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
Route::post('/register', [AuthController::class, 'register']);

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

    // acceso mixto, lectura para Gestor, CRUD completo para Inventarios y General
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

    // =========================
    // CLIENTES
    // =========================

    // Admin y Gestor pueden listar clientes.
    // Cliente NO puede listar a todos.
    Route::apiResource('clientes', ClienteController::class)
        ->only(['index'])
        ->middleware('role:Admin_General|Gestor_Rentas');

    // Admin y Gestor pueden ver cualquiera.
    // Cliente puede entrar a SHOW, pero la Policy limitará al suyo.
    Route::apiResource('clientes', ClienteController::class)
        ->only(['show'])
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');

    // Solamente Admin General puede crear, modificar o eliminar clientes.
    Route::apiResource('clientes', ClienteController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:Admin_General');


    // =========================
    // RENTAS
    // =========================

    // Admin, Gestor y Cliente pueden consultar.
    // La Policy/Service limitará al Cliente a SUS rentas.
    Route::apiResource('rentas', RentaController::class)
        ->only(['index', 'show'])
        ->middleware('role:Admin_General|Gestor_Rentas|Cliente');
        Route::patch(
    'rentas/{renta}/finalizar',
    [RentaController::class, 'finalizar']
)->middleware('role:Admin_General|Gestor_Rentas');

    // Admin y Gestor tienen CRUD de Rentas.
    Route::apiResource('rentas', RentaController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:Admin_General|Gestor_Rentas');

    // Acción extra
    Route::post('rentas/{renta}/accesorios/{accesorio}', [AccesorioController::class, 'agregarARenta'])
        ->middleware('role:Admin_General|Gestor_Rentas');
});
