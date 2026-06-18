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
mkdir -p "${APP_DIR}/backend/var/cache" "${APP_DIR}/backend/var/log"
chmod -R 777 "${APP_DIR}/backend/var"

# Stop backend before composer to avoid Symfony cache race (entrypoint vs post-install scripts).
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" stop backend || true
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm --no-deps backend \
  sh -c 'rm -rf var/cache/* && composer run-script deploy-install --no-interaction'

docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d backend backend-nginx

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
