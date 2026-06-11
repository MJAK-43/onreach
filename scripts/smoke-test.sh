#!/bin/sh
set -e

echo "=== On'Reach Smoke Test ==="

check_url() {
  name="$1"
  url="$2"
  if curl -sf "$url" > /dev/null; then
    echo "[OK] $name — $url"
  else
    echo "[FAIL] $name — $url"
    exit 1
  fi
}

check_url "API Health" "http://localhost:8081/health"
check_url "AI Health" "http://localhost:8000/health"
check_url "Frontend" "http://localhost:5173"
check_url "MinIO Console" "http://localhost:9001"

echo "=== PostgreSQL ==="
docker compose exec -T postgres psql -U "${POSTGRES_USER:-onreach}" -d "${POSTGRES_DB:-onreach}" -c "SELECT 1 AS ok;"
docker compose exec -T postgres psql -U "${POSTGRES_USER:-onreach}" -d "${POSTGRES_DB:-onreach}" -c "SELECT extname FROM pg_extension WHERE extname = 'vector';"

echo "=== Redis ==="
docker compose exec -T redis redis-cli SET onreach:smoke test
docker compose exec -T redis redis-cli GET onreach:smoke

echo "=== All smoke tests passed ==="
