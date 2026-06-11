# Rapport Sprint 1.1 — Consolidation Auth & RBAC

**Branche :** `feature/auth-rbac`  
**Date :** 11 juin 2026  
**Prérequis :** Sprint 1 (base auth/RBAC)

## Verdict

**SPRINT 1.1 VALIDÉ** — bloquants merge traités, couverture tests significativement augmentée.

**Note : 82/100**

## Livrables

| Livrable | Statut |
|----------|--------|
| Audit trail automatique (User, Role, Permission) | ✅ `AuditTrailDoctrineListener` |
| Logs sécurité ROLE_UPDATED / PERMISSION_UPDATED | ✅ `RoleProcessor`, `PermissionProcessor` |
| Garde-fou rôles système (permissions vides) | ✅ |
| Forgot-password avec envoi email | ✅ `PasswordResetMailer` |
| Route refresh token Gesdinet | ✅ `config/routes/gesdinet_jwt_refresh_token.yaml` |
| Tests backend (27) | ✅ MFA, RBAC, audit, mailer, refresh, voter |
| Tests frontend (13) | ✅ ProtectedRoute, PermissionGate, UsersPage |

## Tests

| Suite | Avant | Après |
|-------|-------|-------|
| PHPUnit | 11 | **27** |
| Vitest | 6 | **13** |
| Couverture estimée | ~45 % | **~65 %** |

Objectif 85 % : non atteint — backlog Sprint 2 (CRUD frontend, tests E2E).

## Configuration ajoutée

```env
MAILER_DSN=null://null          # dev : null://null, prod : smtp://...
MAILER_FROM=noreply@onreach.inovixora.fr
FRONTEND_URL=http://localhost:5173
```

## Fichiers clés

- `backend/src/Infrastructure/Doctrine/AuditTrailDoctrineListener.php`
- `backend/src/Infrastructure/Mail/PasswordResetMailer.php`
- `backend/src/Infrastructure/ApiPlatform/RoleProcessor.php`
- `backend/src/Infrastructure/ApiPlatform/PermissionProcessor.php`
- `backend/config/routes/gesdinet_jwt_refresh_token.yaml`

## Recommandation merge

La branche peut être fusionnée vers `develop` après :
1. Validation CI GitHub (pipelines verts)
2. Smoke test DEV post-déploiement
3. Configuration `MAILER_DSN` réel sur le serveur DEV
