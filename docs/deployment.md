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
| `develop` | DEV | `deploy-dev.yml` (preflight → CI → deploy) |
| `preprod` | PREPROD | `deploy-preprod.yml` |
| `main` | PROD | `deploy-prod.yml` |

## Pipeline develop (anti-régression)

Chaque push sur `develop` exécute **dans l'ordre** :

1. **Preflight** — scripts shell en LF, syntaxe `bash -n`
2. **Backend CI** — CS Fixer, PHPStan, PHPUnit, audit Composer
3. **Frontend CI** — ESLint, TypeScript, Vitest, build
4. **Deploy** — uniquement si les 3 étapes précédentes réussissent

Le déploiement serveur arrête le backend avant `composer install --no-scripts` pour éviter les courses sur `var/cache`.

### Avant de pousser sur develop (local)

```powershell
# Windows
./scripts/quality.ps1
```

```bash
# Linux / macOS / Git Bash
./scripts/quality.sh
```

### Fins de ligne

Les fichiers `.sh` et `.php` sont forcés en **LF** via `.gitattributes` et `.editorconfig`. Ne pas committer de scripts avec CRLF (Windows).

### Hook Git local (optionnel)

```bash
bash scripts/install-git-hooks.sh
```

Bloque le push vers `develop` / `main` / `preprod` si `./scripts/quality.sh` échoue.

### Protection GitHub (recommandé)

Dans **Settings → Branches → Branch protection rules** pour `develop` :

- Require status checks : `preflight / checks`, `backend-quality / quality`, `frontend-quality / quality`
- Require branches to be up to date before merging
- Do not allow bypassing the above settings

Ainsi, aucun merge vers `develop` ne déclenche un déploiement sans CI verte.

## HTTPS

Traefik est configuré avec l'entrypoint `websecure` (port 443).
Ajouter les certificats TLS via Let's Encrypt ou certificats internes au Sprint 1.

## Secrets

- Utiliser les GitHub Secrets pour CI/CD
- Utiliser Symfony Secrets ou un vault en production
- Ne jamais committer `.env`
