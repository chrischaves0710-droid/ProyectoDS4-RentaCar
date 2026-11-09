# Renta-Car en ASP.NET Core 8

Porción vertical del Hito 2. La API usa EF Core 8 con SQLite y tokens Bearer opacos. El modo Development aplica las migraciones y carga los datos ficticios en una base vacía. En Production no se cargan usuarios de prueba ni se ejecutan migraciones al iniciar. Para un despliegue se requiere configurar HTTPS, la conexión y el aprovisionamiento de usuarios fuera de este conjunto de prueba.

## Ejecutar y comprobar

Desde la raíz de la solución, con el SDK .NET 8 x64 disponible:

```powershell
dotnet restore
dotnet build --no-restore
dotnet test --no-build
$env:ASPNETCORE_ENVIRONMENT = 'Development'
$env:ConnectionStrings__RentaCar = "Data Source=$PWD\rentacar-benchmark.sqlite"
dotnet run --project Api --no-build --no-launch-profile --urls http://127.0.0.1:5182
```

En otra consola:

```powershell
$base = 'http://127.0.0.1:5182'
$login = Invoke-RestMethod "$base/api/login" -Method Post -ContentType application/json -Body (@{
    email = 'inventario@example.test'
    password = 'RentaCar-Ficticio-2026!'
} | ConvertTo-Json)
Invoke-RestMethod "$base/api/vehiculos?page=1&per_page=10" -Headers @{ Authorization = "Bearer $($login.token)" }
```

Swagger UI en http://127.0.0.1:5182/swagger y OpenAPI en http://127.0.0.1:5182/swagger/v1/swagger.json. El usuario ficticio cliente@example.test tiene la misma contraseña de prueba y acceso de lectura. El token expira en 30 minutos. POST /api/logout con el token actual lo revoca y queda fuera del comparativo.

## Datos para el benchmark

El archivo Api/fixtures/comparativo.json conserva los tres vehículos del contrato e incluye sus catálogos, una renta que impide eliminar el vehículo 1 y dos usuarios ficticios. Para Laravel y NestJS, importar catálogos y usuarios antes de insertar vehículos y rentas con IDs explícitos. Generar los hashes de contraseña mediante las herramientas de cada framework. Si un esquema requiere columnas adicionales, como precio_diario en Laravel, acordar valores fijos antes de medir y registrar esa diferencia.

Antes de cada corrida, detener el servidor y arrancar con una ruta nueva de SQLite dedicada al benchmark. Cambiar ConnectionStrings__RentaCar a esa ruta. Se aplicará la migración y se cargará el mismo JSON. Usar una instancia a la vez, en la misma máquina y con el mismo escenario. Excluir la preparación de la base y el inicio de sesión del escenario de carga si el equipo mide solo operaciones de vehículos.

## Decisiones provisionales del contrato

El contrato congelado quedó en su repositorio original. Esta API usa 401 para credenciales incorrectas, 422 con errores por campo para entradas inválidas, 204 al eliminar y 409 si el vehículo tiene una renta asociada. El listado acepta page, per_page y q, ordena por ID y devuelve data y meta. Las respuestas de detalle y escritura contienen data con los ocho campos acordados. El alta incluye Location.

Laravel devuelve 422 para credenciales incorrectas y 204 al eliminar, usa limit y añade links, alias y precio_diario. Su validación exige precio_diario y su servicio aplica reglas adicionales de kilometraje y estado. En esta porción el estado se envía como una clave válida y la eliminación se restringe por cualquier renta asociada. Los roles reproducen los grupos de lectura y escritura de Laravel. La equivalencia entre frameworks sigue pendiente de una verificación HTTP común.

## SDK instalado en esta sesión

El SDK se instaló de forma aislada. En esta máquina se puede usar anteponiéndolo al PATH de la consola:

```powershell
$env:PATH = 'C:\Users\1\Documents\Codex\2026-11-09\te-d\work\dotnet;' + $env:PATH
dotnet --list-sdks
```
