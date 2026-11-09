# Puesta en marcha
Inicio: 2026-11-09T15:46:41.9422983-06:00
Instalación del SDK excluida.
Comandos: dotnet new sln; dotnet new webapi --no-openapi; dotnet sln add Api; dotnet run --project Api --no-launch-profile --urls http://127.0.0.1:5182

## Repetición controlada desde carpeta vacía
El primer intento inició durante la extracción del SDK y falló al resolver Microsoft.NET.Sdk.Web. El segundo no obtuvo HTTP en el entorno restringido. Ambos quedaron fuera del cálculo.
Inicio: 2026-11-09T15:51:01.3920639-06:00
Primer HTTP 200: 2026-11-09T15:51:05.8648487-06:00
Segundos: 4.4702299
Comandos: dotnet new sln -n RentaCar; dotnet new webapi -n Api --no-openapi --no-restore; dotnet sln add Api; dotnet build -p:NuGetAudit=false; dotnet Api/bin/Debug/net8.0/Api.dll --urls http://127.0.0.1:5184; GET /weatherforecast.
La medición incluye generación, compilación, arranque y sondeo. El endpoint medido pertenece a la plantilla. SDK 8.0.427 x64; ASP.NET Core 8.0.33.

## Conteos medidos de código propio

Se contaron líneas físicas no vacías de archivos C# escritos para el proyecto, incluidas declaraciones e importaciones. Se excluyeron bin, obj, la migración generada y su snapshot, configuración, scripts, datos y documentación. Resultado: 11 archivos C# y 473 líneas, distribuidos en 10 archivos de API (396 líneas) y 1 archivo de pruebas (77 líneas).

## Estimaciones de esfuerzo por área

| Área | Archivos y líneas | Alcance del conteo |
|---|---|---|
| Validación | 7 archivos, 66 líneas | VehiculoInput completo (28), Errors completo (9), Program líneas 21–28 (8), VehiculoService desde la declaración de errors hasta la salida temprana y el bloque catch de placa duplicada (13), dos líneas de LoginInput, firma de listado con restricciones y dos comprobaciones de errores del controlador (4), índice único y colación de placa en Database (2). |
| Autenticación y autorización | 6 archivos, 103 líneas | Bearer completo (55), AuthController completo (27), registro y middleware en Program (6), cuatro atributos y la constante de roles en VehiculosController (5), modelos Usuario y Token (2), registro de sets, índices y relaciones y creación del hash de la contraseña de prueba en Database (8). |
| OpenAPI | 3 archivos, 62 líneas de configuración específica | ContractDocumentation completo (53), registro y middleware en Program (8) y referencia de paquete en Api.csproj (1). Los atributos de respuesta se mantienen junto a los endpoints. |
| Pruebas de integración | 1 archivo, 77 líneas | ContractTests.cs. Tres escenarios xUnit con WebApplicationFactory y SQLite temporal por caso. |

Los conteos por área son estimaciones de esfuerzo basadas en la selección manual de código. Las áreas pueden compartir líneas. No se midieron minutos de autoría por área.

SDK .NET 8.0.427 x64, ASP.NET Core y runtime .NET 8.0.33, EF Core SQLite y Design 8.0.28, dotnet-ef 8.0.28, Swashbuckle.AspNetCore 6.6.2, Microsoft.AspNetCore.Mvc.Testing 8.0.28, Microsoft.NET.Test.Sdk 17.11.1, xUnit 2.9.2 y runner Visual Studio 2.8.2. La autenticación usa ASP.NET Core Authentication e Identity PasswordHasher del framework compartido y criptografía de .NET. Reutiliza EF Core SQLite para persistir los hashes de tokens.

Para reproducir los conteos base desde PowerShell en la raíz del repositorio:

```powershell
$files = Get-ChildItem Api,Tests -Filter *.cs -Recurse -File |
    Where-Object { $_.FullName -notmatch '[\\/](bin|obj|Migrations)[\\/]' }
$files | ForEach-Object {
    [pscustomobject]@{ Archivo = $_.FullName; Lineas = @(Get-Content $_.FullName | Where-Object { $_ -match '\S' }).Count }
}
```

## Reproducción de la migración y las pruebas

La migración incluida se generó con dotnet-ef 8.0.28 ejecutando dotnet ef migrations add InitialSchema --project Api --output-dir Infrastructure/Migrations. Para repetir el build y las pruebas, seguir README.md. Después de iniciar el servidor local, ejecutar ./smoke.ps1 con PowerShell 7 para comprobar los seis endpoints y las operaciones documentadas en OpenAPI. El script crea y elimina un vehículo de prueba, por lo que debe ejecutarse sobre una base descartable.

El SDK se instaló mediante el script oficial de Microsoft en una carpeta aislada, ya que winget no estaba disponible en el entorno de ejecución. La descarga y las solicitudes TCP de loopback requirieron el contexto de ejecución autorizado fuera del sandbox. El tiempo de arranque se midió con una plantilla vacía en una repetición controlada.

## Resultado de la verificación final

Restauración y compilación completadas en C:\proyectos\rentacar-aspnet, con cero errores y cero advertencias. xUnit ejecutó 3 pruebas de integración, con 3 aprobadas, 0 fallidas y 0 omitidas. Las solicitudes HTTP a Kestrel confirmaron las seis operaciones, los códigos esperados, Location, los errores por campo y la lectura posterior a escritura en SQLite. El documento OpenAPI respondió 200 y se comprobaron su versión 3, el esquema Bearer y las respuestas declaradas de las seis operaciones. La equivalencia HTTP con Laravel y NestJS queda pendiente.

## Mediciones pendientes del integrante 3

Peticiones por segundo, latencia p95, memoria residente en reposo y tamaño del artefacto desplegable. La comprobación smoke valida funcionalidad y no genera métricas de rendimiento.
