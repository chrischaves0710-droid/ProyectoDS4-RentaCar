<?php

use App\Exceptions\ClienteException;
use App\Exceptions\EstadoException;
use App\Exceptions\VehiculoException;
use App\Exceptions\RentaException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        //
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Respuestas JSON para la API
        |--------------------------------------------------------------------------
        */

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') || $request->expectsJson(),
        );

        /*
        |--------------------------------------------------------------------------
        | 400 Bad Request
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (BadRequestHttpException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => 'La solicitud no es válida.',
                    ], 400);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 401 Unauthorized
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (AuthenticationException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => 'No autenticado. Debe proporcionar credenciales válidas.',
                    ], 401);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 403 Forbidden
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (AuthorizationException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => 'No tiene permisos para realizar esta operación.',
                    ], 403);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 404 Not Found
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (NotFoundHttpException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => 'El recurso solicitado no existe.',
                    ], 404);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 409 Conflict - Cliente
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (ClienteException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                    ], 409);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 409 Conflict - Vehículo
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (VehiculoException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                    ], 409);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 409 Conflict - Estado
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (EstadoException $e, Request $request) {
                if ($request->is('api/*')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                    ], 409);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | 422 Unprocessable Content - Validación
        |--------------------------------------------------------------------------
        */

        $exceptions->render(
            function (ValidationException $e, Request $request) {
                if ($request->is('api/*')) {
                    if ($request->isJson() && !empty($request->getContent())) {
                        json_decode($request->getContent());
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            return response()->json([
                                'message' => 'La solicitud no es válida.',
                            ], 400);
                        }
                    }

                    return response()->json([
                        'message' => 'Los datos proporcionados no son válidos.',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }
        );
        /*
|--------------------------------------------------------------------------
| 409 Conflict - Renta
|--------------------------------------------------------------------------
*/

$exceptions->render(
    function (RentaException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }
    }
);

    })

    ->create();