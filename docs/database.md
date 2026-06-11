# Base de données

## PostgreSQL + pgvector

- Image Docker : `pgvector/pgvector:pg16`
- Extension `vector` activée via `docker/postgres/init.sql`

## Configuration

```env
DATABASE_URL=postgresql://onreach:PASSWORD@postgres:5432/onreach?serverVersion=16&charset=utf8
```

## Migrations

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
docker compose exec backend php bin/console doctrine:migrations:migrate
```

## Seeders

Script placeholder : `backend/scripts/seed.sh`

## Backups

```bash
docker compose exec backend sh scripts/backup.sh
```

Les sauvegardes sont stockées dans le volume configuré (`BACKUP_DIR`).

## Redis

| Usage | Configuration |
|-------|---------------|
| Cache | `REDIS_URL` + `cache.yaml` |
| Queue | `MESSENGER_TRANSPORT_DSN=redis://redis:6379/messages` |
| Sessions | À configurer Sprint 1 |

## MinIO

| Bucket | Usage |
|--------|-------|
| `documents` | Documents étudiants |
| `uploads` | Fichiers uploadés |
| `exports` | Exports CSV/PDF |

Buckets à créer via la console MinIO au Sprint 1.
