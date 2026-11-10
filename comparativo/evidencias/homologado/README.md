# Entrega técnica de homologación IF0009

Las ejecuciones del 10 de noviembre de 2026 corresponden a b1051510a37ddb9e68a6cab38d8f78cc4c6efc05. El commit de evidencias posterior incorpora resultados y scripts. El contrato congelado y los archivos históricos del integrante 3 conservan su contenido. La adenda técnica sigue pendiente de aprobación académica.

## Reproducción en Windows

Usar PowerShell 7, Git, Python 3, PHP 8.4 con PDO SQLite, Composer 2, Node 24 con npm y SDK .NET 8 x64. Instalar dependencias y compilar desde la raíz del repositorio.

```powershell
composer install
Push-Location comparativo/nestjs
npm ci
npm run build
npm test -- --runInBand
npm run test:e2e -- --runInBand
Pop-Location
dotnet build comparativo/aspnet/RentaCar.sln
dotnet test comparativo/aspnet/RentaCar.sln --no-build
$runner = 'comparativo/evidencias/homologado/probar-contrato.ps1'
foreach ($target in 'laravel','nestjs','aspnet') {
    pwsh -NoProfile -File $runner -Framework $target -OutputDirectory C:\temp\rentacar-resultados
    if ($LASTEXITCODE) { throw "Falló el contrato para $target" }
}
```

El coordinador inicia un proceso local, confirma los datos de la SQLite descartable, ejecuta las 18 aserciones y detiene únicamente ese proceso. La prueba de conflicto usa un cuarto vehículo temporal vinculado a una renta, fuera de las tres filas del contrato. Los usuarios de lectura y escritura son ficticios. Laravel y NestJS los suministran mediante configuración. ASP.NET los siembra como registros con contraseña hasheada.

Para medir, instalar k6 y repetir el bucle con el modificador -Benchmark y el mismo directorio de resultados. El coordinador exige el mismo SHA limpio, el mismo hash del fixture y 18/18 para los tres. Se usó k6 2.3.0, PHP 8.4.27 con OPcache CLI activo y validación de timestamps deshabilitada, Node 24.22.2 y SDK .NET 8.0.427 con runtime ASP.NET 8.0.33. Las tres instancias usaron una sola computadora Windows y se ejecutaron secuencialmente en el orden Laravel, NestJS y ASP.NET. Los binarios PHP, Node y k6 se prepararon de forma aislada.

Los datos SQL se crearon de nuevo al iniciar cada framework. Se comprobó igualdad de vehículos, categorías, estados, rentas y restricciones de referencias. Las tres repeticiones de cada framework solo leyeron datos después de autenticar y mantuvieron el mismo estado. Cada framework abrió un archivo SQLite propio. Los nombres físicos de columnas, los índices auxiliares y la persistencia de identidad difieren. El procedimiento reproduce el mismo estado de dominio y el mismo motor, sin afirmar que se usó un archivo binario común ni una versión idéntica de la biblioteca SQLite. Se consultaron 3.53.4 en PHP PDO y 3.53.2 en better-sqlite3, y se verificó 3.41.2 en la DLL nativa de SQLite de ASP.NET.

## Verificación

| Framework | Pruebas propias | Contrato común |
|---|---|---:|
| Laravel 13.31.0 | 128 Pest, 353 aserciones, PHP 8.4.27 y SQLite en memoria | 18/18 |
| NestJS | 4 Jest unitarias y 4 e2e con SQLite. OpenAPI en /api/openapi.json | 18/18 |
| ASP.NET Core 8 | 3 xUnit y smoke.ps1 con los seis endpoints y OpenAPI | 18/18 |

La suite común valida cuerpos JSON, no solo códigos. Cubre autenticación y autorización, lectura paginada y escritura con validación, placa duplicada, actualización y conflicto por dependencia. Los resultados HTTP no contienen tokens ni contraseñas.

## Rendimiento y huella de ejecución

Se hicieron 10 lecturas de calentamiento por servidor y tres corridas principales de 30 s a 10 solicitudes/s, con 5 VUs preasignados y máximo 20. Se hizo una cuarta corrida de k6 para muestreo de memoria. Se tomaron 30 muestras de WorkingSet64 por condición con intervalos de aproximadamente 1 s. El reposo se midió después de las corridas principales, con el proceso vivo. Los valores son MiB del proceso servidor. Los JSON de k6 y CSV de memoria se conservan junto con los metadatos. El archivo resumen.csv reporta los valores exactos.

| Framework | HTTP 200 | RPS efectivos medios | Latencia media ms | Media de p95 ms | Working Set medio reposo MiB | Working Set medio carga MiB |
|---|---:|---:|---:|---:|---:|---:|
| Laravel | 902 | 10.01 | 45.34 | 72.53 | 51.32 | 51.33 |
| NestJS | 903 | 10.03 | 2.90 | 5.03 | 91.98 | 92.10 |
| ASP.NET Core | 901 | 10.01 | 2.98 | 6.26 | 200.43 | 200.94 |

Las 2 706 solicitudes principales tuvieron cero errores. El p95 es la media de tres percentiles por corrida. La carga fija no mide capacidad máxima. El Working Set del proceso no equivale a memoria privada, heap o huella de todo el equipo. Los modos de servidor y autenticación difieren: PHP integrado con OPcache, Node ejecutando dist y dotnet con compilación Debug. El intento Laravel sin OPcache tuvo 184 respuestas, 116 iteraciones perdidas y p95 de 3 874.12 ms. Está separado y excluido de los promedios. Estos resultados representan las implementaciones y condiciones medidas.

## Ocho criterios del enunciado

| Criterio | Evidencia y alcance |
|---|---|
| Puesta en marcha | puesta-en-marcha.csv. Desde carpetas vacías hasta el primer 200 con JSON: Laravel 1.44 s, NestJS 14.67 s y ASP.NET 7.06 s. Una repetición por framework con dependencias ya disponibles, sin instalación de paquetes dentro del cronómetro. |
| Tamaño de código | conteo-detalle.csv. Líneas no vacías de implementación por framework: Laravel 177 (4 archivos), NestJS 466 (18) y ASP.NET 396 (10). Las pruebas se cuentan por separado. Se excluyen dependencias y migraciones generadas. Pueden contarse comentarios. Se incluye únicamente el bloque de rutas de benchmark de Laravel. |
| Validación | esfuerzo-detalle.csv delimita bloques verificables. Laravel 21, NestJS 64 y ASP.NET 53 líneas no vacías seleccionadas. Incluye lógica y configuración, con alcance explícito en el CSV. |
| Autenticación | 35, 89 y 86 líneas seleccionadas. Laravel usa tokens opacos hasheados por el perfil comparativo, Nest usa @nestjs/jwt y ASP.NET usa AuthenticationHandler y persistencia EF. |
| OpenAPI | 74 líneas seleccionadas para NestJS y 59 para ASP.NET, que usa Swashbuckle. Laravel mantiene docs/openapi.yaml para la API habitual. El perfil de benchmark Laravel carece de especificación separada, por lo que la comparación de este criterio es parcial. |
| Pruebas | 18 aserciones en tres escenarios por framework y tres matrices JSON. La suite externa compartida tiene 107 líneas no vacías. No se registraron minutos de autoría. |
| Rendimiento | Nueve JSON principales más tres JSON de carga simultánea con memoria. Tasas, p95 y latencias medias en resumen.csv y metadatos individuales. |
| Huella de ejecución | 60 muestras por framework. artefactos.csv suma bytes sin compresión y sin runtimes globales: Laravel 27 594 035, NestJS 106 747 944 y ASP.NET 10 203 177. |

Los conteos de esfuerzo describen bloques de código y pueden solaparse. No se interpretan como minutos de implementación. Para seguridad por omisión, distinguir las funciones que provee el framework de las configuraciones del proyecto. Nest activa explícitamente ValidationPipe y guards. ASP.NET configura Bearer, autorización y respuesta 422 sobre el comportamiento de ApiController. Laravel usa validación y middleware del framework y añade autenticación específica de benchmark. El perfil habitual usa Sanctum y conserva su flujo.

## Límite académico

La eliminación exitosa devuelve 204 conforme al PDF y difiere del 200 del contrato congelado. La adenda documenta esa diferencia sin darla por aprobada. El fixture y las pruebas se ciñen a la porción vertical del benchmark. La aplicación Laravel habitual continúa usando sus reglas de negocio y formatos de salida.

## Paquetes y archivos de resultados

Los CSV y JSON de la sesión están en ejecucion-b105151. empaquetar.ps1 y medir-artefactos.py reproducen la huella de disco en una carpeta nueva. Laravel incluye el backend versionado, Composer de producción y las entradas del fixture. En la copia empaquetada se desactivó la optimización del autoloader para evitar el bloqueo observado en esa etapa. NestJS incluye dist, npm ci --omit=dev y el fixture. ASP.NET usa dotnet publish -c Release -r win-x64 --self-contained false. Los tamaños excluyen SDK, runtimes del sistema, bases de datos de ejecución y logs de pruebas. Los paquetes incluyen distinto trabajo auxiliar de cada ecosistema. Consultar el desglose por fuente, configuración, código generado y dependencias antes de interpretar la diferencia.

Para reproducir la configuración PHP de la corrida, activar zend_extension=opcache, opcache.enable_cli=1 y opcache.validate_timestamps=0 en el php.ini aislado de CLI. El coordinador aplica output_buffering=4096 al proceso Laravel. Se verificó Composer con check-platform-reqs en PHP 8.4.27.

Para repetir los conteos, ejecutar contar-homologado.py y contar-esfuerzo.py con dos argumentos: la raíz del repositorio y el CSV de salida. medir-arranque.ps1 prepara una carpeta nueva por framework con un endpoint mínimo y mide hasta el primer 200. Esta repetición excluye tiempo de descarga e instalación previa. Los CSV de tiempos producidos en este equipo usan coma decimal.
