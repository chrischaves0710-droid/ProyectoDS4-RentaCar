<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $solicitud): JsonResponse
    {
        $usuario = User::where('email', $solicitud->email)->first();

        // mensaje que no distingue entre cuenta inexistente o clave incorrecta
        if (! $usuario || ! Hash::check($solicitud->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Capacidades asociadas al token basadas en el rol
        $capacidades = $usuario->roles()->pluck('name')->toArray();

        $token = $usuario->createToken(
            name: 'api',
            abilities: $capacidades,
            expiresAt: now()->addMinutes(30)
        );

        return response()->json([
            'token' => $token->plainTextToken
        ], 200);
    }
}