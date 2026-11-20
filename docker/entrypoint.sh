#!/bin/sh
set -eu

# Read runtime environment before caching Laravel configuration.
php /usr/local/bin/rentacar-validate-runtime.php
php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

# FrankenPHP receives SIGTERM directly and performs graceful shutdown.
exec "$@"
