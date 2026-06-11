#!/bin/bash
set -euo pipefail

BACKUP_ROOT="/opt/onreach/backups"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)
ENV_FILE="/opt/onreach/app/.env.dev"
COMPOSE="docker compose -f /opt/onreach/app/deployments/docker-compose.dev.yml --env-file $ENV_FILE"

mkdir -p "$BACKUP_ROOT/postgres" "$BACKUP_ROOT/minio"

# PostgreSQL
$COMPOSE exec -T postgres sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' \
  | gzip > "$BACKUP_ROOT/postgres/onreach_${DATE}.sql.gz"

# MinIO
docker run --rm \
  --network onreach_dev \
  -v "$BACKUP_ROOT/minio:/backup" \
  --env-file "$ENV_FILE" \
  --entrypoint /bin/sh \
  minio/mc:latest -c '
    mc alias set local http://minio:9000 "$MINIO_ROOT_USER" "$MINIO_ROOT_PASSWORD"
    mc mirror local/documents /backup/documents_'"$DATE"' --quiet
    mc mirror local/uploads /backup/uploads_'"$DATE"' --quiet
    mc mirror local/exports /backup/exports_'"$DATE"' --quiet
  '

find "$BACKUP_ROOT/postgres" -name '*.sql.gz' -mtime +${RETENTION_DAYS} -delete
find "$BACKUP_ROOT/minio" -maxdepth 1 -type d -mtime +${RETENTION_DAYS} -exec rm -rf {} +

echo "Backup completed: $DATE"
