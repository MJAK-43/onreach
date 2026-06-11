# Audit Sprint 1 — Auth & RBAC — On'Reach

**Date** : 11 juin 2026  
**Branche** : `feature/auth-rbac`  
**Base de comparaison** : `develop`  
**Auditeur** : Principal Architect (automatisé)  
**Verdict** : **SPRINT 1 NON VALIDÉ** — note **74/100**  
**Fusion vers `develop`** : **NON RECOMMANDÉE** en l'état (voir § Bloquants merge)

---

## Résumé exécutif

Le Sprint 1 livre une **base auth/RBAC fonctionnelle** : login JWT RS256, refresh token, MFA TOTP, voter permissions, CRUD API users (Api Platform), listes admin frontend. Les corrections récentes (`symfony/expression-language`, `#[Ignore]` sur `User::getRoles()`) débloquent les pages admin en local.

En revanche, plusieurs exigences documentées restent **incomplètes** : audit trail sans écriture automatique, forgot-password sans envoi email, couverture tests très en dessous de l'objectif 85 %, admin frontend en lecture seule, logs `ROLE_UPDATED` / `PERMISSION_UPDATED` jamais émis.

### Corrections validées pendant l'audit

| Problème | Correction | Statut |
|----------|------------|--------|
| `GET /api/users` → 500/400 (`getRoles()` vs relation Doctrine) | `#[Ignore]` sur `User::getRoles()` | ✅ Corrigé |
| Api Platform `security:` → 500 sans expression-language | `symfony/expression-language` dans `composer.json` | ✅ Corrigé |
| Pages admin « Impossible de charger… » | Dépend des 2 corrections ci-dessus | ✅ OK en local |
| PHPStan OOM local (128 Mo) | Passe avec `--memory-limit=512M` ; CI non impactée | ⚠️ Doc |

---

## Phase 1 — Périmètre Sprint 1

| Livrable attendu (`docs/auth.md`, `docs/rbac.md`, `docs/security.md`) | Statut |
|-----------------------------------------------------------------------|--------|
| Auth JWT RS256 + refresh | ✅ |
| Login / logout / refresh / me | ✅ |
| Forgot / reset / change password | ⚠️ Forgot sans email réel |
| MFA setup / enable / disable + recovery codes | ✅ backend ; ⚠️ UI partielle |
| 6 rôles + 10 permissions système | ✅ seed `app:seed:rbac` |
| API users CRUD | ✅ backend ; ❌ frontend |
| API roles/permissions PATCH | ✅ backend ; ❌ frontend |
| PermissionVoter + SUPER_ADMIN bypass | ✅ |
| Rate limiting login | ✅ |
| Security logs (écriture) | ✅ login, MFA, MDP |
| Audit trail (historisation PATCH) | ❌ service prêt, jamais appelé |
| Couverture tests ≥ 85 % | ❌ ~45 % estimée |
| Déploiement DEV post-merge | ⏳ Non validé |

---

## Phase 2 — Backend (78/100)

### Architecture DDD/CQRS

| Couche | Fichiers clés | Statut |
|--------|---------------|--------|
| Domain | `PasswordPolicy`, `SystemRole`, `SystemPermission`, `SecurityEventType` | ✅ |
| Application | 11 handlers auth (login, MFA, MDP…) | ✅ |
| Infrastructure | `PermissionVoter`, `MfaService`, `UserProcessor`, `SeedRbacCommand` | ✅ |
| Presentation | `AuthController`, `MeController` | ✅ |
| Entités | 8 entités + 1 migration (`Version20250611160000`) | ✅ |

**Écart** : `symfony/workflow` installé mais non utilisé (stub commenté).

### Endpoints

**Auth (contrôleurs)**

| Route | Statut |
|-------|--------|
| `POST /api/auth/login` | ✅ |
| `POST /api/auth/logout` | ✅ |
| `POST /api/auth/refresh` | ✅ (Gesdinet) |
| `GET /api/me` | ✅ |
| `POST /api/auth/forgot-password` | ⚠️ Token créé, pas d'email |
| `POST /api/auth/reset-password` | ✅ |
| `POST /api/auth/change-password` | ✅ |
| `POST /api/auth/mfa/setup\|enable\|disable` | ✅ |

**RBAC (Api Platform)**

| Ressource | GET | POST | PATCH | DELETE |
|-----------|-----|------|-------|--------|
| `/api/users` | ✅ | ✅ | ✅ | ✅ |
| `/api/roles` | ✅ | — | ✅ | — |
| `/api/permissions` | ✅ | — | ✅ | — |
| `/api/audit_trails` | ✅ lecture | — | — | — |
| `/api/security_logs` | ✅ collection | — | — | — |

### Qualité locale (11 juin 2026)

| Vérification | Résultat |
|--------------|----------|
| PHPUnit | ✅ 11/11 |
| PHPStan niveau 6 | ✅ 0 erreur (`--memory-limit=512M`) |
| PHP CS Fixer | ✅ 0 fichier à corriger |
| `composer validate --strict` | ✅ |

### Risques identifiés

| Sévérité | Risque |
|----------|--------|
| **Élevé** | `AuditTrailService` jamais injecté/appelé — table `audit_trails` toujours vide |
| **Élevé** | Forgot-password : aucun `Mailer` — flux incomplet en dev/prod |
| **Moyen** | Logout n'invalide que le refresh token (JWT access valide jusqu'à TTL) |
| **Moyen** | MFA setup persiste le secret avant activation |
| **Moyen** | Rôles `isSystem` modifiables via PATCH sans garde-fou |
| **Moyen** | `ROLE_UPDATED` / `PERMISSION_UPDATED` définis mais jamais loggés |
| **Faible** | `expiresIn: 3600` hardcodé dans `LoginCommandHandler` |
| **Faible** | Pas de contraintes Validator sur entités User/Role |

---

## Phase 3 — Frontend (72/100)

### Routes (12)

Login, forgot/reset password, unauthorized, dashboard, settings (placeholder), profile, security (MDP/MFA), admin users/roles/permissions.

### Intégration API

| Zone | Statut |
|------|--------|
| `api.ts` — auth, refresh 401, Hydra `member` | ✅ |
| `auth.ts` — tokens localStorage | ✅ |
| `ProtectedRoute` + `PermissionGate` | ✅ (3 permissions `.view`) |
| CRUD admin (POST/PATCH users, PATCH roles) | ❌ non branché |
| Page logs audit (`system.logs`) | ❌ absente |

### Qualité locale

| Vérification | Résultat |
|--------------|----------|
| ESLint | ✅ |
| TypeScript | ✅ |
| Vitest | ✅ 6/6 |
| `npm run build` | ✅ |

### Écarts UX vs admin RBAC typique

- Listes en **lecture seule** (pas de création user, assignation rôles, matrice permissions)
- Colonne rôles absente sur liste utilisateurs
- Section « Administration » visible même sans permissions admin
- `/admin/security` = sécurité personnelle (libellé trompeur)

---

## Phase 4 — Base de données & seed (85/100)

| Élément | Statut |
|---------|--------|
| Migration unique 9 tables | ✅ |
| Seed RBAC + super admin | ✅ `app:seed:rbac` |
| Entrypoint Docker migrate + seed | ✅ `docker/entrypoint.sh` |
| Compte test | `admin@onreach.inovixora.fr` / `Admin@OnReach12!` |

---

## Phase 5 — Sécurité (80/100)

| Mesure OWASP (`docs/security.md`) | Statut |
|-----------------------------------|--------|
| Brute force (rate limiter email + IP) | ✅ |
| Anti-énumération forgot-password | ✅ |
| Refresh single-use | ✅ |
| Verrouillage compte (5 échecs) | ✅ |
| MFA TOTP + 8 recovery codes | ✅ |
| CORS configuré | ✅ |
| Politique MDP 12 car. + complexité | ✅ |

---

## Phase 6 — Tests & couverture (52/100)

### Backend — 11 tests

| Fichier | Couverture |
|---------|------------|
| `AuthControllerTest` | Login, me, forgot-password |
| `PasswordPolicyTest` | 3 cas |
| `HealthControllerTest` + health domain | Sprint 0 |

**Non testé** : MFA, refresh, logout, change/reset password, PermissionVoter, CRUD Api Platform, rate limiting, audit.

### Frontend — 6 tests

`LoginPage` (4), `Sidebar` (1), `DashboardPage` (1).

**Non testé** : `ProtectedRoute`, `PermissionGate`, `api.ts`, pages admin, MFA, profil.

### Couverture estimée

| Couche | Estimation | Objectif |
|--------|------------|----------|
| Backend auth/RBAC | ~40 % | 85 % |
| Frontend auth/admin | ~20 % | 85 % |
| **Global Sprint 1** | **~45 %** | **85 %** |

---

## Phase 7 — CI/CD & déploiement (65/100)

| Workflow | Statut |
|----------|--------|
| `backend.yml` | Configuré (PostgreSQL + Redis + JWT keys CI) |
| `frontend.yml` | Configuré |
| `feature-pr.yml` | PR auto feature → develop |
| `deploy-dev.yml` | Sur push `develop` |

**Commits sur `feature/auth-rbac` vs `develop`** : 3 commits, ~94 fichiers, +6218 / −189 lignes.

**CI distante** : non vérifiable localement (`gh` indisponible). Valider manuellement sur GitHub avant merge.

**Checklist post-merge DEV** :
- `https://api.onreach.inovixora.fr/health`
- Login + `/api/me`
- `GET /api/users`, `/api/roles`, `/api/permissions`
- Migrations + seed sur serveur
- Clés JWT + `symfony/expression-language` dans image backend

---

## Phase 8 — Documentation (88/100)

| Fichier | Statut |
|---------|--------|
| `docs/auth.md` | ✅ |
| `docs/rbac.md` | ✅ |
| `docs/security.md` | ✅ |
| `docs/SPRINT1_REPORT.md` | ✅ (à jour via cet audit) |
| `docs/AUDIT_SPRINT1.md` | ✅ ce document |

---

## Bloquants merge vers `develop`

| # | Bloquant | Action requise |
|---|----------|----------------|
| 1 | Couverture tests ~45 % (objectif 85 %) | Ajouter tests MFA, RBAC, CRUD API, guards frontend |
| 2 | Audit trail non fonctionnel | Brancher `AuditTrailService` (subscribers Api Platform) |
| 3 | Forgot-password sans email | Configurer `symfony/mailer` + template |
| 4 | CI distante non confirmée | Vérifier pipelines verts sur GitHub |
| 5 | Déploiement DEV non validé | Smoke test post-merge |

## Non bloquants (Sprint 1.1 ou backlog)

- Admin frontend CRUD (backend déjà prêt)
- Garde-fous `isSystem` sur PATCH roles/permissions
- Page logs `system.logs`
- Révocation JWT access token au logout
- Suppression dépendance `symfony/workflow` si inutile
- PHPStan memory-limit dans script `composer quality`

---

## Note finale : 74/100

| Critère | Poids | Score |
|---------|-------|-------|
| Architecture DDD/CQRS | 12 % | 85 |
| API Auth & RBAC backend | 18 % | 82 |
| Sécurité (JWT, MFA, OWASP) | 15 % | 80 |
| Frontend auth/admin | 12 % | 72 |
| Base de données & seed | 8 % | 85 |
| Tests & couverture | 20 % | 52 |
| CI/CD & déploiement | 10 % | 65 |
| Documentation | 5 % | 88 |

---

## Verdict

# ❌ SPRINT 1 NON VALIDÉ

La branche `feature/auth-rbac` **ne doit pas être fusionnée vers `develop`** tant que les 5 bloquants ci-dessus ne sont pas traités ou explicitement dérogés par le product owner.

**Prochaine étape recommandée** : Sprint 1.1 sur la même branche — tests (priorité MFA + RBAC), audit trail, mailer forgot-password — puis re-audit avant merge.
