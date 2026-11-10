#requires -Version 7
param(
    [Parameter(Mandatory)][ValidateSet('laravel','nestjs','aspnet')][string]$Framework,
    [Parameter(Mandatory)][string]$OutputDirectory,
    [ValidateRange(1024,65535)][int]$Port = 5210,
    [switch]$Benchmark
)

$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path "$PSScriptRoot/../../..").Path
$fixture = "$repo/comparativo/aspnet/Api/fixtures/comparativo.json"
$fixtureHash = (Get-FileHash $fixture).Hash
$users = (Get-Content -Raw $fixture | ConvertFrom-Json).usuarios
$runtime = @{laravel='php'; nestjs='node'; aspnet='dotnet'}[$Framework]
$exe = (Get-Command $runtime -CommandType Application | Select-Object -First 1).Source
$python = (Get-Command python -CommandType Application | Select-Object -First 1).Source
$sha = & git -C $repo rev-parse HEAD
if ($LASTEXITCODE) { throw 'No se pudo identificar el commit.' }
$dirty = @(git -C $repo status --porcelain --untracked-files=no).Count -gt 0
New-Item -ItemType Directory $OutputDirectory -Force | Out-Null
$out = (Resolve-Path $OutputDirectory).Path
if ($Benchmark) {
    if ($dirty) { throw 'El benchmark requiere un árbol limpio.' }
    foreach ($target in 'laravel','nestjs','aspnet') {
        $prior = Get-Content -Raw "$out/$target-http.json" | ConvertFrom-Json
        if ($prior.commit -ne $sha -or $prior.modified_tracked_files -or $prior.passed -ne 18 -or $prior.failed -ne 0 -or $prior.fixture_sha256 -ne $fixtureHash) {
            throw "Falta una verificación 18/18 reproducible del commit y fixture actuales para $target."
        }
    }
    $k6 = (Get-Command k6 -CommandType Application | Select-Object -First 1).Source
}
$root = (Resolve-Path ([IO.Path]::GetTempPath())).Path
$temp = Join-Path $root ('rentacar-contract-' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory $temp | Out-Null
$db = "$temp/fixture.sqlite"
$base = "http://127.0.0.1:$Port"
$vars = @{BENCHMARK_WRITER=$users[0].email; BENCHMARK_READER=$users[1].email; BENCHMARK_PASSWORD=$users[0].password}
$secret = [Security.Cryptography.RandomNumberGenerator]::GetBytes(32)
$envPrevious = @{}
$server = $null

switch ($Framework) {
    laravel {
        $vars += @{
            APP_ENV='benchmark'; APP_KEY=('base64:'+[Convert]::ToBase64String($secret))
            APP_DEBUG='false'; DB_URL=''; DB_CONNECTION='sqlite'; DB_DATABASE=$db
            DB_FOREIGN_KEYS='true'; CACHE_STORE='array'; SESSION_DRIVER='array'; QUEUE_CONNECTION='sync'
            APP_CONFIG_CACHE="$temp/config.php"; APP_ROUTES_CACHE="$temp/routes.php"
        }
        $arguments = "-d output_buffering=4096 -S 127.0.0.1:$Port index.php"
        $directory = "$repo/public"
    }
    nestjs {
        $vars += @{
            RENTACAR_PROFILE='benchmark'; BENCHMARK_FIXTURE=$fixture
            BENCHMARK_DEPENDENCY_CASE=$(if($Benchmark){'0'}else{'1'})
            BENCHMARK_DB_PATH=$db; NEST_BENCHMARK_JWT_SECRET=[Convert]::ToHexString($secret)
            PORT="$Port"; HOST='127.0.0.1'
        }
        $arguments = 'dist/main.js'
        $directory = "$repo/comparativo/nestjs"
    }
    aspnet {
        $vars += @{ASPNETCORE_ENVIRONMENT='Development'; ConnectionStrings__RentaCar="Data Source=$db"}
        $arguments = "bin/Debug/net8.0/Api.dll --urls $base"
        $directory = "$repo/comparativo/aspnet/Api"
    }
}

try {
    foreach ($entry in $vars.GetEnumerator()) {
        $envPrevious[$entry.Key] = [Environment]::GetEnvironmentVariable($entry.Key)
        [Environment]::SetEnvironmentVariable($entry.Key, $entry.Value)
    }
    if ($Framework -eq 'laravel') {
        $seedArgs = @("$PSScriptRoot/preparar-sqlite.py",$db,$fixture)
        if (!$Benchmark) { $seedArgs += '--dependency-case' }
        & $python @seedArgs
        if ($LASTEXITCODE) { throw 'No se pudo preparar SQLite.' }
    }
    $serverArgs = @{
        FilePath=$exe; ArgumentList=$arguments; WorkingDirectory=$directory; PassThru=$true
        WindowStyle='Hidden'; RedirectStandardOutput="$temp/stdout.log"; RedirectStandardError="$temp/stderr.log"
    }
    $server = Start-Process @serverArgs
    $ready = $false
    for ($attempt=0; $attempt -lt 40; $attempt++) {
        if ($server.HasExited) { break }
        try {
            $r = Invoke-WebRequest "$base/api/vehiculos" -SkipHttpErrorCheck -TimeoutSec 1
            if ($r.StatusCode -eq 401) { $ready=$true; break }
        } catch {}
        Start-Sleep -Milliseconds 250
    }
    if (!$ready -or !(Test-Path -LiteralPath $db)) { throw 'El servidor o la base descartable no están disponibles.' }

    $verifyArgs = @("$PSScriptRoot/verificar-sqlite.py",$db,$fixture,$Framework)
    if (!$Benchmark) { $verifyArgs += '--dependency-case' }
    & $python @verifyArgs
    if ($LASTEXITCODE) { throw 'Los datos de prueba no coinciden con el fixture.' }
    $meta = @{
        framework=$Framework; commit=$sha; modified_tracked_files=$dirty; runtime=((& $exe --version) -join ' ')
        engine='SQLite'; fixture_sha256=$fixtureHash; timestamp_utc=[DateTimeOffset]::UtcNow.ToString('o')
    }
    if ($Benchmark) {
        & "$PSScriptRoot/medir.ps1" -Base $base -Server $server -OutputDirectory $out -Metadata $meta -K6 $k6
    } else {
        $report = & $python "$repo/comparativo/evidencias/contrato-http.py" $base --framework $Framework --dependency-id 4 | ConvertFrom-Json
        $passed = @($report.results | Where-Object passed).Count
        $meta += @{passed=$passed; failed=$report.results.Count-$passed; results=$report.results}
        $meta | ConvertTo-Json -Depth 10 | Set-Content -Encoding utf8 "$out/$Framework-http.json"
        "$Framework $passed/$($report.results.Count)"
        if ($passed -ne 18 -or $report.results.Count -ne 18) { throw 'El contrato común no pasó completo.' }
    }
} finally {
    if ($server -and !$server.HasExited) {
        Stop-Process -Id $server.Id -ErrorAction SilentlyContinue
        $server.WaitForExit()
    }
    foreach ($entry in $envPrevious.GetEnumerator()) {
        [Environment]::SetEnvironmentVariable($entry.Key,$entry.Value)
    }
    foreach ($stream in 'stdout','stderr') {
        if (Test-Path "$temp/$stream.log") { Copy-Item "$temp/$stream.log" "$out/$Framework-$stream.log" }
    }
    $resolved = (Resolve-Path -LiteralPath $temp).Path
    if ((Split-Path $resolved -Parent) -eq $root -and (Split-Path $resolved -Leaf) -match '^rentacar-contract-[a-f0-9]{32}$') {
        Remove-Item -LiteralPath $resolved -Recurse -Force
    }
}
