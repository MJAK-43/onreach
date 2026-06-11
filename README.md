# On'Reach

Plateforme SaaS d'accompagnement des étudiants internationaux — CRM, Agent IA, RAG, gestion des candidatures, paiements et rendez-vous.

> **Sprint 0** : fondations techniques uniquement. Aucun module métier implémenté.

## Stack

| Couche | Technologies |
|--------|-------------|
| Frontend | React 19, TypeScript, Vite, Tailwind CSS, Shadcn UI, React Router, TanStack Query |
| Backend | Symfony 7, API Platform, Doctrine, JWT, Messenger, Workflow |
| Database | PostgreSQL 16 + pgvector |
| Cache / Queue | Redis |
| Stockage | MinIO |
| IA | FastAPI (service séparé) |
| Infra | Docker, Traefik |
| Monitoring | Grafana, Prometheus, Loki |
| Observabilité | OpenTelemetry |

## Structure

```
onreach/
├── .github/workflows/    # CI/CD
├── .cursor/              # Règles Cursor
├── docs/                 # Documentation
├── docker/               # Config infra (Traefik, Prometheus, Loki, Grafana)
├── frontend/             # React 19
├── backend/              # Symfony 7 (DDD/CQRS)
├── ai-service/           # Service IA
└── docker-compose.yml
```

## Démarrage rapide

### 1. Configuration

```bash
cp .env.example .env
```

Éditer `.env` et définir des secrets forts pour :
- `APP_SECRET`
- `POSTGRES_PASSWORD`
- `MINIO_ROOT_PASSWORD`
- `JWT_PASSPHRASE`
- `GRAFANA_ADMIN_PASSWORD`

### 2. Lancer l'infrastructure

```bash
docker compose up -d --build
```

### 3. Vérifier les services

| Service | URL |
|---------|-----|
| Frontend | http://frontend.localhost |
| API Health | http://api.localhost/health |
| API Docs | http://api.localhost/api/docs |
| AI Health | http://ai.localhost/health |
| MinIO Console | http://minio.localhost |
| Grafana | http://localhost:3000 |
| Traefik Dashboard | http://localhost:8080 |

### 4. Développement local (sans Docker)

**Backend :**
```bash
cd backend
composer install --ignore-platform-req=ext-redis
cp .env .env.local  # configurer DATABASE_URL
php bin/phpunit
```

**Frontend :**
```bash
cd frontend
npm install
npm run dev
```

## Tests & Qualité

### Backend
```bash
cd backend
composer quality    # CS Fixer + PHPStan + PHPUnit + audit
```

### Frontend
```bash
cd frontend
npm run lint
npm run typecheck
npm run test
npm run build
```

## Déploiement

| Branche | Environnement | Workflow |
|---------|---------------|----------|
| `develop` | DEV | `deploy-dev.yml` |
| `preprod` | PREPROD | `deploy-preprod.yml` |
| `main` | PROD | `deploy-prod.yml` |

Voir [docs/deployment.md](docs/deployment.md).

## Git Flow

```
feature/* → develop → preprod → main
```

Voir [docs/gitflow.md](docs/gitflow.md).

## Documentation

- [Architecture](docs/architecture.md)
- [Base de données](docs/database.md)
- [Déploiement](docs/deployment.md)
- [Workflow CI/CD](docs/workflow.md)
- [DevSecOps](docs/devsecops.md)
- [Agent IA](docs/ai-agent.md)

## Prochaines étapes — Sprint 1

1. **Authentification** — JWT login/register, gestion des rôles
2. **Module Candidats** — CRUD, entités Domain, API Platform
3. **Module Admissions** — Workflow Symfony
4. **RAG** — Ingestion documents Campus France / Visa / Parcoursup
5. **Agent IA** — Orchestration multi-agents
6. **Intégrations** — Brevo, WhatsApp, Gmail via n8n
7. **Dashboard** — KPIs décisionnels

## Licence

Propriétaire — Inovixora

# test deploy
