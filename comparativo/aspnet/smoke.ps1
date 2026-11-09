param([string]$Base = 'http://127.0.0.1:5182')

$ErrorActionPreference = 'Stop'
function Request($Method, $Path, $Status, $Body = $null) {
    $argsForHttp = @{ Uri = "$Base$Path"; Method = $Method; SkipHttpErrorCheck = $true; TimeoutSec = 5 }
    if ($script:token) { $argsForHttp.Headers = @{ Authorization = "Bearer $script:token" } }
    if ($null -ne $Body) {
        $argsForHttp.ContentType = 'application/json'
        $argsForHttp.Body = $Body | ConvertTo-Json -Compress
    }
    $result = Invoke-WebRequest @argsForHttp
    if ([int]$result.StatusCode -ne $Status) { throw "$Method $Path esperaba $Status y recibió $($result.StatusCode): $($result.Content)" }
    Write-Host "$Method $Path -> $Status"
    $result
}

$script:token = $null
Request GET /api/vehiculos 401 | Out-Null
Request POST /api/login 401 @{ email = 'inventario@example.test'; password = 'incorrecta' } | Out-Null
$script:token = (Request POST /api/login 200 @{
    email = 'inventario@example.test'; password = 'RentaCar-Ficticio-2026!'
}).Content | ConvertFrom-Json | Select-Object -ExpandProperty token
$page = (Request GET '/api/vehiculos?page=1&per_page=1&q=Corolla' 200).Content | ConvertFrom-Json
if ($page.data[0].placa -ne 'ABC-123' -or $page.meta.total -ne 1) { throw 'Listado inesperado.' }
Request GET /api/vehiculos/1 200 | Out-Null
$vehicle = @{ placa = 'SMOKE-' + (Get-Random); marca = 'Toyota'; modelo = 'Yaris'; anno = 2024;
    kilometraje = -1; categoria_id = 1; estado_id = 1 }
$validation = (Request POST /api/vehiculos 422 $vehicle).Content | ConvertFrom-Json
if (!$validation.errors.kilometraje) { throw 'Falta el error por campo.' }
$vehicle.kilometraje = 100
$created = Request POST /api/vehiculos 201 $vehicle
$id = ($created.Content | ConvertFrom-Json).data.id
$route = "/api/vehiculos/$id"
if ([string]$created.Headers.Location[0] -ne "$Base$route") { throw 'Location inesperado.' }
$vehicle.kilometraje = 110
Request PUT $route 200 $vehicle | Out-Null
if (((Request GET $route 200).Content | ConvertFrom-Json).data.kilometraje -ne 110) { throw 'No se persistió la actualización.' }
Request DELETE /api/vehiculos/1 409 | Out-Null
Request DELETE $route 204 | Out-Null
Request GET $route 404 | Out-Null
$doc = (Request GET /swagger/v1/swagger.json 200).Content | ConvertFrom-Json -AsHashtable
if ($doc.openapi -notlike '3.*' -or $doc.components.securitySchemes.Bearer.scheme -ne 'bearer') {
    throw 'Definición OpenAPI inesperada.'
}
foreach ($entry in @(
    @('/api/login', 'post', '200,401,422'),
    @('/api/vehiculos', 'get', '200,401,403,422'),
    @('/api/vehiculos', 'post', '201,401,403,422'),
    @('/api/vehiculos/{id}', 'get', '200,401,403,404'),
    @('/api/vehiculos/{id}', 'put', '200,401,403,404,422'),
    @('/api/vehiculos/{id}', 'delete', '204,401,403,404,409')
)) {
    foreach ($code in $entry[2].Split(',')) {
        if (!$doc.paths[$entry[0]][$entry[1]].responses[$code]) {
            throw "OpenAPI no declara $($entry[1]) $($entry[0]) $code"
        }
    }
}
Write-Host 'Verificación HTTP y OpenAPI completada.'
