<?php

declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "Configuración del contenedor inválida: {$message}\n");
    exit(1);
}

if (getenv('APP_ENV') !== 'production') {
    fail('APP_ENV debe ser production.');
}

if (!in_array(strtolower((string) getenv('APP_DEBUG')), ['false', '0'], true)) {
    fail('APP_DEBUG debe ser false o 0.');
}

$key = (string) getenv('APP_KEY');
$decoded = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
if (!is_string($decoded) || strlen($decoded) !== 32) {
    fail('APP_KEY debe contener una clave AES-256 válida de 32 bytes.');
}

$url = (string) getenv('APP_URL');
if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
    fail('APP_URL debe ser una URL HTTP o HTTPS válida.');
}

$port = filter_var(getenv('PORT'), FILTER_VALIDATE_INT);
if ($port === false || $port < 1024 || $port > 65535) {
    fail('PORT debe ser un puerto entre 1024 y 65535.');
}

$connection = (string) getenv('DB_CONNECTION');
if (!in_array($connection, ['sqlite', 'mysql', 'mariadb', 'pgsql'], true)) {
    fail('DB_CONNECTION debe ser sqlite, mysql, mariadb o pgsql.');
}

if ($connection === 'sqlite') {
    $database = (string) getenv('DB_DATABASE');
    if (!str_starts_with($database, '/') || !is_file($database) || !is_writable($database) || !is_writable(dirname($database))) {
        fail('DB_DATABASE debe apuntar a un archivo SQLite absoluto y escribible en un volumen preparado.');
    }
} elseif (!getenv('DB_URL')) {
    foreach (['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $name) {
        if (getenv($name) === false || getenv($name) === '') {
            fail("Falta {$name} para la conexión externa.");
        }
    }
}

foreach (['/app/storage', '/app/bootstrap/cache'] as $directory) {
    if (!is_writable($directory)) {
        fail("El usuario del contenedor requiere escritura en {$directory}.");
    }
}
