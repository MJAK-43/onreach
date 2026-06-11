# Rapport Sprint 1 — Auth & RBAC

**Branche :** `feature/auth-rbac`  
**Date :** 11 juin 2026

## Verdict

**SPRINT 1 NON VALIDÉ** — base fonctionnelle livrée, couverture tests < 85% objectif.

**Note : 72/100**

## Résumé

| Domaine | Statut | Note |
|---------|--------|------|
| Architecture DDD/CQRS | OK | 85 |
| Sécurité (JWT, MFA, RBAC) | OK | 80 |
| API Auth | OK | 85 |
| Frontend Auth/Admin | OK | 75 |
| Base de données | OK | 80 |
| Tests | Partiel | 55 |
| Déploiement DEV | À valider CI | 60 |

## Tests

- Backend : 11 tests PHPUnit — **100% succès**
- Frontend : 6 tests Vitest — **100% succès**
- PHPStan niveau 6 — **0 erreur**
- ESLint + TypeScript — **OK**
- Couverture estimée : ~45% (objectif 85% non atteint)

## Corrections restantes

1. Augmenter couverture tests (MFA, permissions, API CRUD)
2. Valider déploiement DEV post-merge
3. Envoi email réel forgot-password (Mailer configuré, transport à brancher)
4. Event subscribers audit automatique sur PATCH entités

## Endpoints DEV à vérifier post-déploiement

- https://dev.onreach.inovixora.fr
- https://api.onreach.inovixora.fr/health
- https://api.onreach.inovixora.fr/api/auth/login
