#requires -Version 7
param(
    [Parameter(Mandatory)][string]$Repository,
    [Parameter(Mandatory)][string]$OutputDirectory,
    [ValidateRange(1024,65535)][int]$Port = 5222
)
$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path $Repository).Path
$folder = (New-Item -ItemType Directory -Path $OutputDirectory).FullName
$measurements = @()
$startUrl = "http://127.0.0.1:$Port"
foreach ($framework in 'laravel','nestjs','aspnet') {
    $dir = (New-Item -ItemType Directory -Path "$folder/$framework").FullName
    $server = $null
    $start = [DateTimeOffset]::UtcNow
    $timer = [Diagnostics.Stopwatch]::StartNew()
    try {
        switch ($framework) {
            laravel {
                New-Item -ItemType Directory -Path "$dir/bootstrap/cache","$dir/storage/logs" -Force | Out-Null
                $autoload = (Join-Path $repo 'vendor/autoload.php').Replace('\','/')
                $program = '<?php
require ''AUTOLOAD'';
$app = Illuminate\Foundation\Application::configure(basePath: __DIR__)
    ->withRouting(using: function () {
        Illuminate\Support\Facades\Route::get("/health", fn () => ["status" => "ok"]);
    })->withExceptions()->create();
$app->handleRequest(Illuminate\Http\Request::capture());
'
                $program.Replace('AUTOLOAD',$autoload.Replace("'","\'")) | Set-Content -Encoding utf8 "$dir/router.php"
                $exe = (Get-Command php -CommandType Application | Select-Object -First 1).Source
                $arguments = "-d output_buffering=4096 -S 127.0.0.1:$Port router.php"
                $commands = 'crear router PHP mínimo y arrancar php -d output_buffering=4096 -S 127.0.0.1:PUERTO router.php'
            }
            nestjs {
                New-Item -ItemType Junction -Path "$dir/node_modules" -Target "$repo/comparativo/nestjs/node_modules" | Out-Null
                $program = 'import { Controller, Get, Module } from "@nestjs/common";
import { NestFactory } from "@nestjs/core";
@Controller()
class Health {
    @Get("health") get() { return { status: "ok" }; }
}
@Module({ controllers: [Health] })
class App {}
void NestFactory.create(App, { logger: false }).then(app => app.listen(PORT, "127.0.0.1"));
'
                $program.Replace('PORT',"$Port") | Set-Content -Encoding utf8 "$dir/main.ts"
                '{"compilerOptions":{"module":"NodeNext","moduleResolution":"NodeNext","target":"ES2023","outDir":"dist","experimentalDecorators":true,"emitDecoratorMetadata":true,"skipLibCheck":true},"files":["main.ts"]}' | Set-Content -Encoding utf8 "$dir/tsconfig.json"
                $exe = (Get-Command node -CommandType Application | Select-Object -First 1).Source
                & $exe "$dir/node_modules/typescript/bin/tsc" --project "$dir/tsconfig.json"
                if ($LASTEXITCODE) { throw 'Falló tsc.' }
                $arguments = 'dist/main.js'
                $commands = 'enlazar node_modules ya restaurado, crear app TypeScript mínima, tsc --project tsconfig.json y node dist/main.js'
            }
            aspnet {
                '<Project Sdk="Microsoft.NET.Sdk.Web"><PropertyGroup><TargetFramework>net8.0</TargetFramework><ImplicitUsings>enable</ImplicitUsings></PropertyGroup></Project>' | Set-Content -Encoding utf8 "$dir/Startup.csproj"
                'var app = WebApplication.Create(args); app.MapGet("/health", () => new { status = "ok" }); app.Run();' | Set-Content -Encoding utf8 "$dir/Program.cs"
                $exe = (Get-Command dotnet -CommandType Application | Select-Object -First 1).Source
                & $exe build "$dir/Startup.csproj" -p:NuGetAudit=false | Out-Host
                if ($LASTEXITCODE) { throw 'Falló dotnet build.' }
                $arguments = "bin/Debug/net8.0/Startup.dll --urls $startUrl"
                $commands = 'crear Web SDK net8.0 y app mínima, dotnet build -p:NuGetAudit=false y dotnet bin/Debug/net8.0/Startup.dll --urls URL'
            }
        }
        $server = Start-Process $exe -ArgumentList $arguments -WorkingDirectory $dir -WindowStyle Hidden -PassThru -RedirectStandardError "$dir/stderr.log" -RedirectStandardOutput "$dir/stdout.log"
        $ready = $false
        for ($i=0; $i -lt 80; $i++) {
            if ($server.HasExited) { break }
            try {
                $r = Invoke-WebRequest "$startUrl/health" -SkipHttpErrorCheck -TimeoutSec 1
                if ($r.StatusCode -eq 200 -and ($r.Content | ConvertFrom-Json).status -eq 'ok') {
                    $timer.Stop()
                    $ready = $true
                    break
                }
            } catch {}
            Start-Sleep -Milliseconds 100
        }
        if (!$ready) { throw 'El primer endpoint no respondió 200 con el JSON esperado.' }
        $measurements += [pscustomobject]@{
            framework=$framework; start_utc=$start.ToString('o'); response_utc=[DateTimeOffset]::UtcNow.ToString('o')
            elapsed_seconds=$timer.Elapsed.TotalSeconds; http=200; runtime=((& $exe --version) -join ' ')
            commands=$commands; dependency_installation_included=$false
        }
        $measurements | Export-Csv -NoTypeInformation -Encoding utf8 "$folder/puesta-en-marcha.csv"
    } finally {
        if ($server -and !$server.HasExited) { Stop-Process -Id $server.Id; $server.WaitForExit() }
    }
}
$measurements | Format-Table framework,elapsed_seconds,http
