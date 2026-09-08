<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VehiculosController;

Route::get('/', function () {
    return view('welcome');
});

// ruta para el CRUD de vehiculos
Route::resource('vehiculos', VehiculosController::class);