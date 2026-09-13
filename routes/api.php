<?php

use App\Http\Controllers\Api\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('vehiculos', VehiculoController::class);