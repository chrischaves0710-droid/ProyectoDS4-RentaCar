# Ejecutar desde C:\Users\sgabr\ProyectoDS4-RentaCar
# Cuenta un alcance FIJO de 11, 11 y 10 archivos; NO todo el repositorio.
param(
    [string]$Raiz = 'C:\Users\sgabr\ProyectoDS4-RentaCar',
    [string]$Salida = 'C:\Users\sgabr\pruebas-comparativo\evidencias\codigo'
)
$grupos = @{
  'Laravel' = @(
    'app/Http/Controllers/Api/VehiculoController.php','app/Http/Controllers/Api/LoginController.php',
    'app/Services/VehiculoService.php','app/Models/Vehiculo.php',
    'app/Http/Requests/StoreVehiculoRequest.php','app/Http/Requests/UpdateVehiculoRequest.php',
    'app/Http/Resources/VehiculoResource.php','app/Models/Renta.php',
    'app/Models/Categoria.php','app/Models/Estado.php','routes/api.php'
  )
  'NestJS' = @(
    'comparativo/nestjs/src/app.module.ts','comparativo/nestjs/src/main.ts',
    'comparativo/nestjs/src/auth/auth.controller.ts','comparativo/nestjs/src/auth/auth.service.ts',
    'comparativo/nestjs/src/auth/auth.guard.ts','comparativo/nestjs/src/auth/roles.guard.ts',
    'comparativo/nestjs/src/auth/auth.module.ts',
    'comparativo/nestjs/src/vehiculos/vehiculos.controller.ts',
    'comparativo/nestjs/src/vehiculos/vehiculos.service.ts',
    'comparativo/nestjs/src/vehiculos/vehiculos.module.ts',
    'comparativo/nestjs/src/vehiculos/entities/vehiculo.entity.ts'
  )
  'ASP.NET Core' = @(
    'comparativo/aspnet/Api/Program.cs','comparativo/aspnet/Api/Controllers/AuthController.cs',
    'comparativo/aspnet/Api/Controllers/VehiculosController.cs',
    'comparativo/aspnet/Api/Controllers/ContractDocumentation.cs',
    'comparativo/aspnet/Api/Application/VehiculoInput.cs',
    'comparativo/aspnet/Api/Application/VehiculoService.cs',
    'comparativo/aspnet/Api/Application/Errors.cs',
    'comparativo/aspnet/Api/Domain/Models.cs',
    'comparativo/aspnet/Api/Infrastructure/Database.cs',
    'comparativo/aspnet/Api/Security/Bearer.cs'
  )
}
$detalle = foreach ($f in $grupos.Keys) {
  foreach ($ruta in $grupos[$f]) {
    $absoluta = Join-Path $Raiz ($ruta -replace '/','\')
    if (-not (Test-Path $absoluta)) { throw "No existe: $absoluta" }
    $lineas = @(Get-Content -LiteralPath $absoluta)
    [pscustomobject]@{
      Framework = $f
      Archivo = $ruta
      Lineas_fisicas = $lineas.Count
      Lineas_no_vacias = @($lineas | Where-Object { $_ -match '\S' }).Count
    }
  }
}
New-Item -ItemType Directory -Force -Path $Salida | Out-Null
$detalle | Export-Csv (Join-Path $Salida 'conteo-final-detalle.csv') -NoTypeInformation -Encoding UTF8
$resumen = $detalle | Group-Object Framework | ForEach-Object {
  [pscustomobject]@{
    Framework = $_.Name
    Archivos = $_.Count
    Lineas_fisicas = ($_.Group | Measure-Object Lineas_fisicas -Sum).Sum
    Lineas_no_vacias = ($_.Group | Measure-Object Lineas_no_vacias -Sum).Sum
  }
}
$resumen | Sort-Object Framework | Format-Table -AutoSize
$resumen | Export-Csv (Join-Path $Salida 'conteo-final-resumen.csv') -NoTypeInformation -Encoding UTF8
