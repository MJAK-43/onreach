#!/bin/bash
set -euo pipefail

APP_DIR="/opt/onreach/app"
ENV_FILE="${APP_DIR}/.env.dev"
COMPOSE_FILE="${APP_DIR}/deployments/docker-compose.dev.yml"

cd "$APP_DIR"

bash "${APP_DIR}/deployments/scripts/fix-permissions.sh"

docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build --pull
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d

bash "${APP_DIR}/deployments/scripts/plesk-proxy-onreach.sh"

echo "Deploy DEV completed."
