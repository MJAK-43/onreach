# Contrôles qualité locaux — à lancer avant chaque push vers develop.
$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)

Write-Host "=== Preflight (LF + syntaxe shell) ===" -ForegroundColor Cyan
$bad = @()
Get-ChildItem -Path "$Root\deployments", "$Root\backend\docker" -Filter "*.sh" -Recurse -ErrorAction SilentlyContinue | ForEach-Object {
    $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
    if ($bytes -contains 13) { $bad += $_.FullName }
}
if ($bad.Count -gt 0) {
    $bad | ForEach-Object { Write-Host "CRLF: $_" -ForegroundColor Red }
    Write-Host "Corrigez avec: git add --renormalize ." -ForegroundColor Yellow
    exit 1
}
Write-Host "Preflight OK"

Write-Host "=== Backend ===" -ForegroundColor Cyan
Push-Location "$Root\backend"
if (-not (Test-Path .env)) { Copy-Item .env.test .env }
composer quality
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Pop-Location

Write-Host "=== Frontend ===" -ForegroundColor Cyan
Push-Location "$Root\frontend"
npm run lint
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
npm run typecheck
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
npm run test
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
npm run build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Pop-Location

Write-Host "=== Tous les controles sont OK - push autorise ===" -ForegroundColor Green
