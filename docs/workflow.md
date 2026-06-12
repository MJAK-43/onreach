# Workflow CI/CD

## Pipelines

| Workflow | Déclencheur | Actions |
|----------|-------------|---------|
| `backend.yml` | Push/PR backend | PHPUnit, PHPStan, CS Fixer, composer audit |
| `frontend.yml` | Push/PR frontend | ESLint, TypeScript, Vitest, build, npm audit |
| `security.yml` | Push/PR + cron | CodeQL, dependency review |
| `feature-pr.yml` | Push `feature/*` | Tests + auto-fix + PR vers develop |
| `deploy-dev.yml` | Push develop | Deploy DEV + PR vers preprod |
| `deploy-preprod.yml` | Push preprod | Deploy PREPROD + PR vers main |
| `deploy-prod.yml` | Push main | Tests + Deploy PROD |

## Qualité

Tous les pipelines **échouent** en cas d'erreur. Aucune désactivation autorisée.

## Auto-fix

- Backend : `php-cs-fixer fix`
- Frontend : `eslint --fix`

## PR automatiques

1. `feature/*` → `develop` (après tests OK)
2. `develop` → `preprod` (après deploy DEV)
3. `preprod` → `main` (après deploy PREPROD, merge manuel requis)

## Sprint Candidat — Mes démarches

Branche : portail candidat `/demarches` + API `/api/me/applications`, `/api/me/campus-france`, etc.

Tests :

```bash
cd backend && php bin/phpunit tests/Presentation/Controller/MeApplicationsApiTest.php
cd frontend && npm test -- --run src/test/Demarches.test.tsx
```

## Sprint 2 — Branche

`feature/sprint-2-candidate` : module Dossier Étudiant Unifié.

Tests requis avant merge :

```bash
cd backend && php bin/phpunit && vendor/bin/phpstan analyse
cd frontend && npm run lint && npm run typecheck && npm test -- --run
docker compose up -d && docker compose exec backend php bin/console doctrine:migrations:migrate
```
