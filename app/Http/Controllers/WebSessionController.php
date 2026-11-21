<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WebSessionController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // Validar los datos del inicio de sesión
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Comprobar las credenciales del usuario
        if (!Auth::guard('web')->attempt($credenciales)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Las credenciales proporcionadas son incorrectas.'
                ],
            ]);
        }

        // Crear una sesión web segura
        $request->session()->regenerate();

        /** @var \App\Models\User $usuario */
        $usuario = Auth::guard('web')->user();

        // Obtener los roles del usuario
        $capacidades = $usuario->roles()
            ->pluck('name')
            ->toArray();

        // Mantener el token utilizado por el frontend
        $token = $usuario->createToken(
            name: 'web',
            abilities: $capacidades,
            expiresAt: now()->addMinutes(30)
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'roles' => $capacidades,
        ], 200);
    }

    public function register(Request $request): JsonResponse
    {
        // Validar los datos del nuevo usuario
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        // Crear el usuario con el rol Cliente
        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'name' => $datos['name'],
                'email' => $datos['email'],
                'password' => $datos['password'],
            ]);

            $usuario->assignRole('Cliente');

            return $usuario;
        });

        // Iniciar sesión web
        Auth::guard('web')->login($usuario);
        $request->session()->regenerate();

        // Generar el token de Sanctum para la API
        $token = $usuario->createToken(
            name: 'web',
            abilities: ['Cliente'],
            expiresAt: now()->addMinutes(30)
        );

        return response()->json([
            'mensaje' => 'Usuario registrado correctamente.',
            'token' => $token->plainTextToken,
            'roles' => ['Cliente'],
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User|null $usuario */
        $usuario = Auth::guard('web')->user();

        // Revocar solamente el token utilizado por este navegador
        $token = $request->bearerToken();

        if ($token && $usuario) {
            $tokenAcceso = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

            if (
                $tokenAcceso &&
                $tokenAcceso->tokenable_type === $usuario->getMorphClass() &&
                (string) $tokenAcceso->tokenable_id === (string) $usuario->getKey()
            ) {
                $tokenAcceso->delete();
            }
        }

        // Cerrar la sesión web de Laravel
        Auth::guard('web')->logout();

        // Invalidar la sesión anterior
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.'
        ], 200);
    }
}
