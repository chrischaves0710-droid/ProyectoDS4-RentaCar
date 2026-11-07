<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Estado;
use Illuminate\Http\JsonResponse;

class CatalogoVehiculosController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'categorias' => Categoria::query()->orderBy('nombre')->get(['id', 'nombre']),
            'estados' => Estado::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }
}
