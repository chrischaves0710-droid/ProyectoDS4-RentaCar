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
    public function __invoke(Request $request): JsonResponse
    {
        $usuario = User::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Las credenciales proporcionadas son incorrectas.'
                ],
            ]);
        }

        $token = $usuario->createToken(
            $request->device_name ?? 'default'
        );

        return response()->json([
            'token' => $token->plainTextToken
        ], 200);
    }
}