# NestJS Renta-Car

## Perfil comparativo local

Node 24, npm y Python 3. Desde esta carpeta:

    npm ci
    npm run build
    npm test -- --runInBand
    npm run test:e2e -- --runInBand

Desde la raíz del repositorio, usa PowerShell 7:

    pwsh -File comparativo/evidencias/homologado/probar-contrato.ps1 -Framework nestjs -OutputDirectory C:\temp\rentacar-resultados

El coordinador inicia la API en loopback con una SQLite nueva, carga el fixture de ASP.NET Core, comprueba datos y relaciones y guarda la matriz HTTP. Para un arranque manual, exporta RENTACAR_PROFILE=benchmark, NEST_BENCHMARK_JWT_SECRET (32 bytes o más), BENCHMARK_WRITER, BENCHMARK_READER y BENCHMARK_PASSWORD. Puedes indicar BENCHMARK_FIXTURE (ruta al JSON canónico), BENCHMARK_DB_PATH (ruta nueva que no exista) y PORT. Sin BENCHMARK_DB_PATH se usa SQLite en memoria y el reinicio restablece los datos. La antigua rentacar.sqlite no se usa.

Los usuarios ficticios se configuran por entorno y representan inventario y cliente. El login devuelve token, un JWT HS256 que expira a los 30 minutos. El guard verifica firma y vencimiento. Este perfil no implementa revocación individual y la suite común verifica un token inválido. OpenAPI está en /api/openapi.json.

## MySQL

Sin RENTACAR_PROFILE=benchmark se conserva el perfil MySQL, con DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD y DB_DATABASE. Requiere un esquema existente. La autenticación también requiere las variables anteriores. Los cambios del perfil SQLite no ejecutan migraciones ni seeds sobre MySQL.
