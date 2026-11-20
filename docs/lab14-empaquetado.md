# Empaquetado de Renta-Car para el Laboratorio 14

Entrega del Integrante 2, verificada el 19 de noviembre de 2026. El trabajo partió del commit `99ba3dc6ffdb05c8add63125cb728c9488088065`. Laravel 13, Blade y Vite se empaquetaron en una imagen Linux AMD64, construida y ejecutada con Docker Engine 26.1.5 en Debian 13 bajo WSL2.

La imagen sirve HTTP mediante FrankenPHP 1.13.6 con Caddy 2.11.9 y PHP 8.4.28 ZTS. Se eligió Alpine para reducir la base y mantener un único proceso servidor. Se verificó esta combinación con las migraciones, vistas, autenticación Sanctum y suite existente. El comportamiento de producción de la aplicación se mantiene con su configuración Laravel habitual.

El `Dockerfile` tiene cinco etapas. `composer-bin` suministra exclusivamente el ejecutable Composer. `php-base` configura PHP de producción e instala las extensiones. `php-deps` instala el lock de Composer con `--no-dev --no-scripts --optimize-autoloader`, comprueba requisitos y ejecuta `package:discover`. `frontend` usa `npm ci` y `npm run build`, con acceso al vendor de compilación para las fuentes de Tailwind. `production` copia el backend, sus dependencias y los recursos compilados. El resultado incluye las migraciones y `docs/openapi.yaml`, servido por una ruta existente. Las etapas de Node y Composer quedan fuera de la imagen ejecutable.

Las tres imágenes están fijadas mediante digest en los argumentos del Dockerfile.

| Etapa | Referencia verificada |
|---|---|
| PHP y servidor | `dunglas/frankenphp:1-php8.4-alpine@sha256:bacc982b5c926a5adee882cd245fc96fb79c316fe9ea8d845e2e3f3863113adb` |
| Frontend | `node:24-alpine@sha256:116b800c3754b440ee0aa3ed2f20f5bf3d99a554bfd9adeea09ea8017f6e85f5` |
| Composer | `composer:2@sha256:919efe8e62179c71dfd31fad035fd671fe7454211c6b2aa30a10af0ab4acedba` |

Las dependencias de aplicación usan los locks versionados. La instalación de extensiones también consulta los repositorios de Alpine. Una reconstrucción posterior puede recibir actualizaciones de esos paquetes, por lo que el ID de imagen y los tamaños se conservan como evidencia de esta ejecución concreta. La actualización deliberada de las bases debe incluir una nueva construcción y verificación.

El usuario final es `app`, UID/GID `10001:10001`, definido mediante `USER`. El código pertenece a root y el usuario de aplicación tiene escritura en `storage`, `bootstrap/cache`, `/config` y `/data`. Se elimina la capacidad de enlazar puertos privilegiados del binario FrankenPHP. Las comprobaciones se ejecutaron con `--cap-drop=ALL --security-opt=no-new-privileges:true`. El puerto predeterminado es `8080`; `PORT` permite usar otro puerto entre 1024 y 65535. El entrypoint usa `exec` para entregar las señales al servidor y el apagado por SIGTERM terminó con código 0.

Se incluyen PDO MySQL/MariaDB, PDO PostgreSQL y PDO SQLite, además de mbstring, intl, zip, bcmath, OPcache y las extensiones de sesión, OpenSSL, XML, ctype, curl y fileinfo. La verificación de requisitos de Composer aprobó sobre PHP 8.4.28. El perfil PDO SQLite fue probado con las 14 migraciones existentes. Las conexiones MySQL y PostgreSQL quedan disponibles para que el integrante 3 seleccione y verifique el proveedor persistente.

La entrada del contenedor valida `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, `PORT` y la configuración mínima de base de datos. Las claves y los valores de entorno llegan al iniciar el proceso. `config:cache` y `view:cache` se ejecutan después de recibirlos. Cambiar la configuración de un contenedor requiere recrearlo. La clave debe administrarse como un secreto persistente del entorno. El arranque no genera claves, ejecuta migraciones ni carga seeders.

| Variable | Requisito de ejecución |
|---|---|
| `APP_KEY` | Clave AES-256 de 32 bytes, normalmente codificada como `base64:`. Debe ser estable por entorno. |
| `APP_URL` | URL pública HTTP o HTTPS. Para la prueba local se usa loopback. |
| `APP_ENV`, `APP_DEBUG` | La imagen fija `production` y `false`, y rechaza valores incompatibles. |
| `DB_CONNECTION` | `sqlite`, `mysql`, `mariadb` o `pgsql`, explícito. |
| `DB_DATABASE` | Nombre de la base externa o ruta absoluta a un SQLite existente y escribible. SQLite necesita escritura también en el directorio padre. |
| `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` | Obligatorios para base externa, salvo que se proporcione `DB_URL`. |
| `DB_PORT`, TLS de base de datos | Se configuran según el proveedor. Validar certificado y parámetros con el integrante 3. |
| `PORT` | `8080` predeterminado; el mapeo del hospedaje debe apuntar al puerto interno elegido. |
| `LOG_CHANNEL` | `stderr` predeterminado para registros del contenedor. |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Elegir y preparar sus dependencias con el despliegue. La prueba usó sesiones y caché en base de datos y cola `sync`. |

El Caddyfile sirve `/app/public`, aplica compresión y registra solicitudes en stdout. El endpoint administrativo está desactivado. La imagen expone HTTP interno; el hospedaje debe proporcionar HTTPS mediante su proxy. El integrante 1 debe coordinar la confianza en proxies, las cookies seguras y CORS con el integrante 3 cuando se conozca la plataforma.

La `.dockerignore` funciona como lista de inclusión. Solo envía los locks, fuentes de aplicación, configuración, migraciones, entradas públicas, recursos Vite, archivos Docker y el YAML de OpenAPI. Quedan fuera `.git`, los `.env`, claves privadas, `vendor` y `node_modules` locales, bases SQLite, logs, pruebas, documentos académicos y `comparativo`. La etapa final copia rutas explícitas. El smoke verificó la ausencia de esos contenidos y de Pest/PHPUnit/Composer en la imagen final.

Se retiró un salto de línea al comienzo de `routes/web.php`, antes de `<?php`. Ese byte emitía salida prematura y cortaba la respuesta binaria del YAML. La corrección de formato permitió recuperar el archivo completo y conservó los 128 casos existentes en verde.

Los comandos siguientes se ejecutan desde la raíz del repositorio con Docker y Buildx disponibles. En esta máquina se utilizó la consola Debian de WSL y `cd /mnt/c/proyectos/ProyectoDS4-RentaCar`.

```sh
docker buildx build --load --progress=plain -t rentacar-lab14:local .
python3 docker/smoke-test.py --image rentacar-lab14:local --output /tmp/rentacar-lab14-smoke
sh docker/regression-test.sh /tmp/rentacar-lab14-regression
```

El smoke crea un volumen y contenedores con nombres únicos. Prepara una SQLite vacía, ejecuta `migrate --force` expresamente, crea un usuario ficticio solo en esa base y prueba la API. Recopila resultados y elimina exclusivamente los recursos que creó. La suite Pest se ejecuta en un contenedor aparte de `php-deps`, al que se copian las pruebas. Instala las dependencias de desarrollo allí y crea un `.env` vacío exclusivo del test. La imagen de producción conserva sus dependencias de producción.

Para repetir la compilación frontend sin usar la caché de esa etapa y consultar sus versiones se puede ejecutar lo siguiente.

```sh
docker buildx build --load --progress=plain --target frontend --no-cache-filter frontend -t rentacar-lab14:frontend .
docker run --rm --entrypoint node rentacar-lab14:frontend --version
docker run --rm --entrypoint npm rentacar-lab14:frontend ls vite laravel-vite-plugin tailwindcss
```

Para probar manualmente una SQLite local se puede usar el siguiente bloque Bash. La generación de clave del ejemplo es una operación explícita para esa prueba descartable.

```sh
export APP_KEY="$(docker run --rm --entrypoint php rentacar-lab14:local -r 'echo "base64:".base64_encode(random_bytes(32));')"
export APP_URL=http://127.0.0.1:18080
export DB_CONNECTION=sqlite
export DB_DATABASE=/app/storage/database.sqlite
volume="rentacar-lab14-local-$(date +%s)"
docker volume create "$volume"
docker run --rm --entrypoint sh -v "$volume:/app/storage" rentacar-lab14:local -c 'touch /app/storage/database.sqlite'
docker run --rm -e APP_KEY -e APP_URL -e DB_CONNECTION -e DB_DATABASE -v "$volume:/app/storage" rentacar-lab14:local php artisan migrate --force --no-interaction
docker run --rm --name rentacar-lab14-local --cap-drop=ALL --security-opt=no-new-privileges:true -p 127.0.0.1:18080:8080 -e APP_KEY -e APP_URL -e DB_CONNECTION -e DB_DATABASE -e QUEUE_CONNECTION=sync -v "$volume:/app/storage" rentacar-lab14:local
```

Para una base externa, suministrar las variables mediante el gestor de secretos del hospedaje y ejecutar `docker run --rm` con la misma imagen y configuración seguido de `php artisan migrate --force --no-interaction`. Corresponde al responsable del despliegue revisar las migraciones, disponer de respaldo y controlar cuándo se ejecutan. Las rutas montadas deben ser escribibles por UID 10001. Las cargas de usuario en `storage/app` necesitan persistencia o un servicio de almacenamiento seleccionado por el equipo.

Los tamaños se midieron sobre archivos regulares de la imagen final. Se sumó `st_size` de cada archivo para frontend y vendor. El tamaño de imagen proviene de `docker image inspect .Size`, que corresponde a sus capas locales sin comprimir. MiB equivale a bytes / 1 048 576. No se midió el tamaño comprimido de publicación en un registro.

| Artefacto | Archivos | Bytes | MiB |
|---|---:|---:|---:|
| `public/build` | 36 | 284 751 | 0,2716 |
| `vendor` de producción | 6 537 | 27 691 734 | 26,4089 |
| Imagen final | Capas Docker | 331 397 015 | 316,0448 |

La imagen medida tiene ID `sha256:9bdc86c383fa6e6b5a18ad70ef5a3f8f1a9e88fa0eac4009b6853f3ca46a60d1`. Se conserva evidencia en `docs/evidencias/lab14-empaquetado/`. Las fechas de los JSON están en UTC; `2026-11-20T00:…Z` corresponde al 19 de noviembre en Costa Rica.

| Verificación | Resultado |
|---|---|
| Construcción multietapa | Terminó y produjo la imagen Linux AMD64. |
| `npm ci` y `npm run build` | Aprobados. Vite 8.2.2, laravel-vite-plugin 3.2.0 y Tailwind 4.3.3. |
| Sintaxis JavaScript | Todos los `.js` de `resources/js` pasaron `node --check`. `package.json` no define una suite JavaScript adicional. |
| Pest | 128 aprobadas, 0 fallidas, 353 aserciones, SQLite en memoria. |
| Smoke | 109 comprobaciones aprobadas, 0 fallidas. |
| HTTP `/up`, `/`, `/login`, `/docs/openapi.yaml` | 200, con descarga completa del YAML. |
| API | 401 sin token; login 200, listado autenticado 200 y logout con revocación exitoso sobre datos ficticios. |
| Vite | Blade apunta al manifiesto. Todos los recursos enumerados se recuperaron con 200 y MIME adecuado, sin orígenes localhost ni cliente de desarrollo. Una entrada JavaScript vacía es un resultado válido de la optimización. |
| Identidad y capacidades | UID 10001, servidor como `app`, capacidades descartadas. |
| Configuración ausente | APP_KEY vacía, debug activo y DB_CONNECTION vacía detienen el contenedor con un error explícito. |
| Healthcheck y parada | `healthy` y apagado por SIGTERM con código 0. |

La advertencia preexistente de PHP sobre el `use Mockery` de una prueba unitaria se mantuvo como diagnóstico; la ejecución final reportó los 128 casos aprobados. Las advertencias de instalación o de servidor que se documenten en las evidencias deben interpretarse por separado de errores de compilación. La prueba local no cubre una conexión externa ni el comportamiento de una plataforma pública.

El integrante 3 debe comunicar el motor y versión de base de datos, requisitos de TLS, puerto interno, configuración del proxy HTTPS, dominio, mecanismos de secretos y persistencia, arquitectura de CPU y límites de recursos. El integrante 4 puede usar `docker buildx build` y el healthcheck como entradas a su CI/CD. El integrante 1 puede incorporar al documento integrador el siguiente texto.

> El backend y frontend web se distribuyen en una imagen multietapa basada en FrankenPHP/Caddy sobre Alpine con PHP 8.4. Composer instala únicamente dependencias de producción y Node compila Vite con los locks del proyecto. La imagen ejecuta el servidor como UID 10001, escucha en 8080 y recibe configuración mediante variables al arrancar. Las migraciones son una operación explícita del despliegue. La verificación local aprobó 128 pruebas Laravel y 109 comprobaciones del contenedor, incluidas /up, login Sanctum y recursos web. La imagen ocupa 316,04 MiB; frontend y vendor ocupan 0,27 y 26,41 MiB respectivamente. El hospedaje aportará HTTPS, secretos y almacenamiento persistente.

Se consultaron las fuentes primarias de [Docker de FrankenPHP](https://frankenphp.dev/docs/docker/) y [despliegue de FrankenPHP](https://frankenphp.dev/docs/production/) para las extensiones, usuario sin privilegios, puerto interno y tratamiento de señales.
