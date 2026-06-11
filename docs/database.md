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

```bash
php bin/console app:seed:rbac
php bin/console app:seed:checklist
```

## Sprint 2 — Tables Candidate

| Table | Description |
|-------|-------------|
| `candidates` | Agrégat principal |
| `academic_profiles`, `academic_records` | Parcours académique |
| `language_profiles`, `language_certificates` | Langues |
| `professional_profiles`, `professional_experiences` | Expérience pro |
| `financing_profiles`, `guarantors` | Financement |
| `candidate_documents` | Documents |
| `candidate_notes` | Notes conseiller |
| `candidate_timeline_entries` | Historique |
| `campus_france_applications` | Campus France |
| `parcoursup_applications`, `parcoursup_wishes` | Parcoursup |
| `paris_saclay_applications` | Paris-Saclay |
| `checklist_templates`, `checklist_items`, `checklist_progress` | Checklist dynamique |

Migration : `Version20260611170300`

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
