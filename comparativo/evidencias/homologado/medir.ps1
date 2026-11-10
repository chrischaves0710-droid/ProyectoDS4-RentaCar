param(
    [Parameter(Mandatory)][string]$Base,
    [Parameter(Mandatory)][Diagnostics.Process]$Server,
    [Parameter(Mandatory)][string]$OutputDirectory,
    [Parameter(Mandatory)][hashtable]$Metadata,
    [Parameter(Mandatory)][string]$K6
)

$ErrorActionPreference = 'Stop'
$framework = $Metadata.framework
$script = (Resolve-Path "$PSScriptRoot/../historico-integrante3/scripts/k6-comparativo.js").Path
$login = Invoke-RestMethod "$Base/api/login" -Method Post -ContentType 'application/json' -Body (@{
    email=$env:BENCHMARK_WRITER; password=$env:BENCHMARK_PASSWORD
} | ConvertTo-Json -Compress)
$headers = @{Authorization="Bearer $($login.token)"}
for ($i=0; $i -lt 10; $i++) {
    $page = Invoke-RestMethod "$Base/api/vehiculos?page=1&per_page=10" -Headers $headers
    if ($page.meta.total -ne 3 -or ($page.data.id -join ',') -ne '1,2,3') { throw 'Página inicial inesperada.' }
}
$previous = @{API_TOKEN=$env:API_TOKEN; BASE_URL=$env:BASE_URL; API_PATH=$env:API_PATH}
$env:API_TOKEN = $login.token
$env:BASE_URL = $Base
$env:API_PATH = '/api/vehiculos?page=1&per_page=10'
$results = @()
function Read-Metrics($path,$name) {
    $metrics = (Get-Content -Raw $path | ConvertFrom-Json).metrics
    $req = $metrics.http_reqs
    $checks = $metrics.checks
    $duration = $metrics.http_req_duration
    $failure = $metrics.http_req_failed.value ?? $metrics.http_req_failed.values.rate
    if ($null -eq $failure -or $failure -ne 0 -or $req.count -lt 300 -or $checks.passes -ne $req.count -or $checks.fails -ne 0) {
        throw "Corrida con métricas inválidas: $path"
    }
    @{
        name=$name; requests=$req.count; success=$checks.passes; errors=$failure
        rps=$req.rate; avg_ms=$duration.avg; p95_ms=$duration.'p(95)'
    }
}
function Sample-Memory($condition) {
    $timer = [Diagnostics.Stopwatch]::StartNew()
    for ($i=0; $i -lt 30; $i++) {
        $remaining = $i*1000 - $timer.ElapsedMilliseconds
        if ($remaining -gt 0) { Start-Sleep -Milliseconds $remaining }
        $Server.Refresh()
        if ($Server.HasExited) { throw 'Servidor cerrado durante el muestreo.' }
        [pscustomobject]@{
            sample=$i+1; utc=[DateTimeOffset]::UtcNow.ToString('o'); condition=$condition
            pid=$Server.Id; process=$Server.ProcessName; working_set_bytes=$Server.WorkingSet64
        }
    }
}
$load = $null
try {
    foreach ($i in 1..3) {
        $path = Join-Path $OutputDirectory "$framework-k6-$i.json"
        & $K6 run --quiet "--summary-export=$path" $script | Out-Null
        if ($LASTEXITCODE) { throw "k6 terminó con código $LASTEXITCODE." }
        $results += Read-Metrics $path "principal-$i"
    }
    $idle = @(Sample-Memory 'reposo')
    $loadPath = Join-Path $OutputDirectory "$framework-k6-ram.json"
    $arguments = 'run --quiet --summary-export="{0}" "{1}"' -f $loadPath,$script
    $load = Start-Process $K6 -ArgumentList $arguments -PassThru -WindowStyle Hidden
    $busy = @(Sample-Memory 'carga')
    $load.WaitForExit()
    if ($load.ExitCode) { throw 'Falló la ejecución k6 de memoria.' }
    $results += Read-Metrics $loadPath 'memoria'
    $idle + $busy | Export-Csv -NoTypeInformation -Encoding utf8 "$OutputDirectory/$framework-memoria.csv"
    $Metadata += @{
        runs=$results; host_os=[Environment]::OSVersion.VersionString
        k6=((& $K6 version) -join ' '); warmup_requests=10; duration_seconds=30
        requested_rate=10; preallocated_vus=5; max_vus=20
        idle_avg_bytes=($idle | Measure-Object working_set_bytes -Average).Average
        idle_max_bytes=($idle | Measure-Object working_set_bytes -Maximum).Maximum
        load_avg_bytes=($busy | Measure-Object working_set_bytes -Average).Average
        load_max_bytes=($busy | Measure-Object working_set_bytes -Maximum).Maximum
    }
    $Metadata | ConvertTo-Json -Depth 10 | Set-Content -Encoding utf8 "$OutputDirectory/$framework-mediciones.json"
} finally {
    if ($load -and !$load.HasExited) { Stop-Process -Id $load.Id -ErrorAction SilentlyContinue }
    foreach ($entry in $previous.GetEnumerator()) {
        [Environment]::SetEnvironmentVariable($entry.Key,$entry.Value)
    }
}
