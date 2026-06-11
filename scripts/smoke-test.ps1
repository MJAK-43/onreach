# On'Reach Smoke Test (Windows PowerShell)
$ErrorActionPreference = "Stop"

Write-Host "=== On'Reach Smoke Test ===" -ForegroundColor Cyan

function Test-Endpoint {
    param([string]$Name, [string]$Url)
    try {
        $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 10
        if ($r.StatusCode -eq 200) {
            Write-Host "[OK] $Name - $Url" -ForegroundColor Green
        } else {
            throw "Status $($r.StatusCode)"
        }
    } catch {
        Write-Host "[FAIL] $Name - $Url - $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

Test-Endpoint "API Health" "http://localhost:8081/health"
Test-Endpoint "AI Health" "http://localhost:8000/health"
Test-Endpoint "Frontend" "http://localhost:5173"
Test-Endpoint "MinIO Console" "http://localhost:9001"

Write-Host "=== PostgreSQL ===" -ForegroundColor Cyan
docker compose exec -T postgres psql -U onreach -d onreach -c 'SELECT 1 AS ok;'
docker compose exec -T postgres psql -U onreach -d onreach -c "SELECT extname FROM pg_extension WHERE extname = 'vector';"

Write-Host "=== Redis ===" -ForegroundColor Cyan
docker compose exec -T redis redis-cli SET onreach:smoke test
docker compose exec -T redis redis-cli GET onreach:smoke

Write-Host "=== All smoke tests passed ===" -ForegroundColor Green
