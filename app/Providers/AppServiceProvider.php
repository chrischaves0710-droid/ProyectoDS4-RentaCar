<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        // Las validaciones HTTP se ejecutan en producción.
        // El contenedor también tiene validaciones de arranque
        // en docker/validate-runtime.php.
        if ($this->app->runningInConsole()) {
            return;
        }

        $errors = [];

        if (config('app.debug') !== false) {
            $errors[] = 'APP_DEBUG debe estar desactivado.';
        }

        $appKey = (string) config('app.key');
        $decodedKey = str_starts_with($appKey, 'base64:')
            ? base64_decode(substr($appKey, 7), true)
            : $appKey;

        if (! is_string($decodedKey) || strlen($decodedKey) !== 32) {
            $errors[] = 'APP_KEY debe contener una clave AES-256 válida.';
        }

        $appUrl = (string) config('app.url');

        if (
            ! filter_var($appUrl, FILTER_VALIDATE_URL)
            || parse_url($appUrl, PHP_URL_SCHEME) !== 'https'
        ) {
            $errors[] = 'APP_URL debe utilizar HTTPS en producción.';
        }

        if (config('session.secure') !== true) {
            $errors[] = 'SESSION_SECURE_COOKIE debe estar activado.';
        }

        if (! empty($errors)) {
            throw new RuntimeException(
                'Configuración de producción insegura: '
                . implode(' ', $errors)
            );
        }
    }
}
