#requires -Version 7
param([Parameter(Mandatory)][string]$Repository,[Parameter(Mandatory)][string]$OutputDirectory)
$ErrorActionPreference = 'Stop'
$PSNativeCommandUseErrorActionPreference = $true
$repo = (Resolve-Path $Repository).Path
$out = (New-Item -ItemType Directory -Path $OutputDirectory).FullName
$fixture = "$repo/comparativo/aspnet/Api/fixtures/comparativo.json"
$initializer = "$repo/comparativo/evidencias/homologado/preparar-sqlite.py"
$revision = git -C $repo rev-parse HEAD
git -C $repo archive --format=zip "--output=$out/laravel-source.zip" $revision app bootstrap config database public resources routes artisan composer.json composer.lock
Expand-Archive "$out/laravel-source.zip" "$out/laravel"
Push-Location "$out/laravel"
try {
    composer config optimize-autoloader false
    composer install --no-dev --no-interaction --prefer-dist --no-scripts
    New-Item -ItemType Directory -Force bootstrap/cache,storage/framework,storage/logs,benchmark | Out-Null
    Copy-Item $fixture,$initializer benchmark
    php artisan --version
} finally { Pop-Location }
Push-Location "$repo/comparativo/nestjs"
try {
    npm run build
    New-Item -ItemType Directory -Path "$out/nestjs" | Out-Null
    Copy-Item package.json,package-lock.json "$out/nestjs"
    Copy-Item dist "$out/nestjs" -Recurse
    Copy-Item $fixture "$out/nestjs/comparativo.json"
} finally { Pop-Location }
Push-Location "$out/nestjs"
try { npm ci --omit=dev --no-audit --no-fund } finally { Pop-Location }
dotnet publish "$repo/comparativo/aspnet/Api/Api.csproj" -c Release -r win-x64 --self-contained false -p:NuGetAudit=false -o "$out/aspnet"
python "$PSScriptRoot/medir-artefactos.py" $out "$out/artefactos.csv"
