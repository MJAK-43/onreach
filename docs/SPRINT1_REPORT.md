# Rapport Sprint 1 — Auth & RBAC

**Branche :** `feature/auth-rbac`  
**Date :** 11 juin 2026  
**Audit complet :** [AUDIT_SPRINT1.md](./AUDIT_SPRINT1.md)

## Verdict

**SPRINT 1** : base livrée (note 74/100) — voir [AUDIT_SPRINT1.md](./AUDIT_SPRINT1.md)  
**SPRINT 1.1** : **VALIDÉ** (note 82/100) — voir [SPRINT1.1_REPORT.md](./SPRINT1.1_REPORT.md)

Fusion vers `develop` **possible** après validation CI + smoke test DEV.

## Résumé

| Domaine | Statut | Note |
|---------|--------|------|
| Architecture DDD/CQRS | OK | 85 |
| Sécurité (JWT, MFA, RBAC) | OK partiel | 80 |
| API Auth | OK | 85 |
| API RBAC (Api Platform) | OK | 82 |
| Frontend Auth | OK | 78 |
| Frontend Admin | Lecture seule | 65 |
| Base de données | OK | 85 |
| Tests | Insuffisant | 52 |
| Déploiement DEV | À valider | 65 |

## Tests (exécution locale 11/06/2026)

- Backend : 11 tests PHPUnit — **100 % succès**
- Frontend : 6 tests Vitest — **100 % succès**
- PHPStan niveau 6 — **0 erreur**
- ESLint + TypeScript + build — **OK**
- Couverture estimée : **~45 %** (objectif 85 % non atteint)

## Corrections récentes (post-audit initial)

1. `symfony/expression-language` — requis pour attributs `security:` Api Platform
2. `#[Ignore]` sur `User::getRoles()` — fix sérialisation `/api/users`
3. Pages admin Utilisateurs / Rôles / Permissions — **OK en local Docker**

## Bloquants avant merge `develop`

1. Couverture tests ≥ 85 % (MFA, RBAC, CRUD API, guards frontend)
2. Brancher `AuditTrailService` sur mutations entités
3. Envoi email réel forgot-password (`symfony/mailer`)
4. Valider CI GitHub (pipelines verts)
5. Smoke test déploiement DEV post-merge

## Backlog Sprint 1.1

- CRUD admin frontend (users, rôles, permissions)
- Garde-fous rôles `isSystem`
- Page logs sécurité (`system.logs`)
- Logs `ROLE_UPDATED` / `PERMISSION_UPDATED`

## Compte seed

`admin@onreach.inovixora.fr` / `Admin@OnReach12!`

## Test local Docker

```powershell
docker compose up -d --build
# Frontend : http://localhost:5173/login
# API      : http://localhost:8081/health
```
