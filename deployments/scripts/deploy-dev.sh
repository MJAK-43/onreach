#!/bin/bash
set -euo pipefail

APP_DIR="/opt/onreach/app"
ENV_FILE="${APP_DIR}/.env.dev"
COMPOSE_FILE="${APP_DIR}/deployments/docker-compose.dev.yml"

cd "$APP_DIR"

bash "${APP_DIR}/deployments/scripts/fix-permissions.sh"

docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" pull
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --remove-orphans

echo "Syncing Composer dependencies (vendor volume may be stale after deploy)..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T backend \
  composer install --no-interaction --ignore-platform-reqs --optimize-autoloader

docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" restart backend backend-nginx

echo "Waiting for backend health..."
for i in $(seq 1 60); do
  if curl -sf http://127.0.0.1:8202/health >/dev/null; then
    echo "Backend healthy"
    break
  fi
  if [ "$i" -eq 60 ]; then
    echo "ERROR: backend health check timed out"
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" logs backend --tail 80 || true
    exit 1
  fi
  sleep 3
done

bash "${APP_DIR}/deployments/scripts/plesk-proxy-onreach.sh"

echo "Deploy DEV completed."
