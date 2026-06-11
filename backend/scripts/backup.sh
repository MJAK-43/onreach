#!/bin/sh
set -e

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="${BACKUP_DIR:-/backups}"
mkdir -p "$BACKUP_DIR"

pg_dump "$DATABASE_URL" > "$BACKUP_DIR/onreach_${TIMESTAMP}.sql"
echo "Backup created: $BACKUP_DIR/onreach_${TIMESTAMP}.sql"
