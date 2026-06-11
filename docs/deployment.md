# Déploiement

## Prérequis

- Docker 24+
- Docker Compose v2
- Git

## Démarrage local

```bash
cp .env.example .env
# Éditer .env avec des secrets forts

docker compose up -d --build
```

## URLs

### Via Traefik (recommandé — nécessite port 80 libre)

> **Attention WAMP** : si Apache WAMP écoute sur le port 80, les domaines `*.localhost` peuvent être interceptés par WAMP au lieu de Traefik. Arrêter Apache WAMP ou utiliser les ports directs ci-dessous.

- Frontend : http://frontend.localhost
- API : http://api.localhost/health
- AI : http://ai.localhost/health
- MinIO : http://minio.localhost
- Traefik Dashboard : http://localhost:8888

### Ports directs (développement / contournement WAMP)

| Service | URL |
|---------|-----|
| API | http://localhost:8081/health |
| API Docs | http://localhost:8081/api/docs |
| Frontend | http://localhost:5173 |
| AI | http://localhost:8000/health |
| MinIO API | http://localhost:9000 |
| MinIO Console | http://localhost:9001 |
| Grafana | http://localhost:3000 |

## Smoke test

```bash
# Linux/macOS
./scripts/smoke-test.sh

# Windows PowerShell
./scripts/smoke-test.ps1
```

## Environnements

| Branche | Environnement | Workflow |
|---------|---------------|----------|
| `develop` | DEV | `deploy-dev.yml` |
| `preprod` | PREPROD | `deploy-preprod.yml` |
| `main` | PROD | `deploy-prod.yml` |

## HTTPS

Traefik est configuré avec l'entrypoint `websecure` (port 443).
Ajouter les certificats TLS via Let's Encrypt ou certificats internes au Sprint 1.

## Secrets

- Utiliser les GitHub Secrets pour CI/CD
- Utiliser Symfony Secrets ou un vault en production
- Ne jamais committer `.env`
