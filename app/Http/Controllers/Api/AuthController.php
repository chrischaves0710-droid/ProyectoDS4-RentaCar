<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $solicitud): JsonResponse
    {
        // Validación con política estricta de contraseñas y k-anonimato
        $solicitud->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);

        $usuario = User::create([
            'name' => $solicitud->name,
            'email' => $solicitud->email,
            'password' => $solicitud->password,
        ]);

        $usuario->assignRole('Cliente');

        $capacidades = $usuario->roles()->pluck('name')->toArray();

        $tokenAcceso = $usuario->createToken(
            name: 'registro',
            abilities: $capacidades,
            expiresAt: now()->addMinutes(30)
        );

        return response()->json([
            'mensaje' => 'Usuario registrado exitosamente',
            'usuario' => $usuario,
            'token' => $tokenAcceso->plainTextToken
        ], 201);
    }
}