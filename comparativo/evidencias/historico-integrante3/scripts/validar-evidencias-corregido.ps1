# PowerShell 5.1 o superior. Solo lectura. No borra, reescribe ni ejecuta pruebas.
param(
    [string]$Carpeta = (Join-Path (Split-Path -Parent $PSScriptRoot) 'evidencias\rendimiento')
)
$ErrorActionPreference = 'Stop'
$Nombres = @(
 'laravel-prueba1.json','laravel-prueba2.json','laravel-prueba3.json',
 'nestjs-auth-prueba1.json','nestjs-auth-prueba2.json','nestjs-auth-prueba3.json',
 'aspnet-prueba1.json','aspnet-prueba2.json','aspnet-prueba3.json',
 'laravel-ram-carga-k6.json','nestjs-ram-carga-k6.json','aspnet-ram-carga-k6.json'
)
$Resultados = @(
 foreach ($Nombre in $Nombres) {
     $Ruta = Join-Path $Carpeta $Nombre
     if (-not (Test-Path -LiteralPath $Ruta -PathType Leaf)) { throw "No existe: $Ruta" }
     $m = (Get-Content -LiteralPath $Ruta -Raw | ConvertFrom-Json).metrics
     if ($null -eq $m -or $null -eq $m.http_req_failed) { throw "Sin metricas de k6: $Ruta" }
     # Versión de --summary-export usada por estas pruebas: .value, NO .values.rate
     $Tasa = $m.http_req_failed.value
     if ($null -eq $Tasa -and $null -ne $m.http_req_failed.values) {
         $Tasa = $m.http_req_failed.values.rate
     }
     if ($null -eq $Tasa) { throw "Formato sin tasa de errores: $Ruta" }
     $Cantidad = [int]$m.http_reqs.count
     $Bien = [int]$m.checks.passes
     $Mal = [int]$m.checks.fails
     [pscustomobject]@{
         Archivo = $Nombre
         Solicitudes = $Cantidad
         HTTP200 = $Bien
         TasaErrores = [double]$Tasa
         Estado = if ([double]$Tasa -eq 0 -and $Bien -eq $Cantidad -and $Mal -eq 0 -and $Cantidad -gt 0) {'VALIDA'} else {'REVISAR'}
     }
 }
)
$Resultados | Format-Table -AutoSize
$Validas = @($Resultados | Where-Object {$_.Estado -eq 'VALIDA'})
Write-Host "Archivos válidos: $($Validas.Count) / $($Nombres.Count)"
if ($Validas.Count -ne $Nombres.Count) { throw 'Hay evidencias que revisar' }
Write-Host 'Validación completa.' -ForegroundColor Green
