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
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" restart backend-nginx

echo "Waiting for backend health..."
for i in $(seq 1 30); do
  if curl -sf http://127.0.0.1:8202/health >/dev/null; then
    echo "Backend healthy"
    break
  fi
  if [ "$i" -eq 30 ]; then
    echo "ERROR: backend health check timed out"
    exit 1
  fi
  sleep 2
done

bash "${APP_DIR}/deployments/scripts/plesk-proxy-onreach.sh"

echo "Deploy DEV completed."
