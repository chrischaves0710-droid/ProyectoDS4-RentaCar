<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $solicitud): JsonResponse
    {
        // Revocación exclusiva del token utilizado en la petición actual
        $solicitud->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente. Token de acceso eliminado.'
        ], 200);
    }
}