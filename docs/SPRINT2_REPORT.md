# Rapport Sprint 2 — Module Candidate

**Date** : 11 juin 2026  
**Branche** : `feature/sprint-2-candidate`  
**Périmètre** : Dossier Étudiant Unifié uniquement (hors paiements, RDV, logement, dashboard métier, RAG, Agent IA)

---

## Note globale : **82 / 100**

| Critère | Note | Commentaire |
|---------|------|-------------|
| Architecture | 88/100 | Agrégat Candidate extensible, services découplés |
| Base de données | 90/100 | Migration complète, relations 1:1/1:N cohérentes |
| API | 85/100 | CRUD + sous-ressources + upload/validation |
| Frontend | 78/100 | Liste, création, fiche avec onglets et complétude |
| Sécurité | 85/100 | RBAC Sprint 1 étendu, filtrage conseiller |
| Tests | 75/100 | 33 tests PHPUnit + 13 Vitest OK ; couverture module ~70% |
| Déploiement DEV | 70/100 | Prêt CI ; deploy DEV après merge `develop` |
| Documentation | 90/100 | `candidate.md`, `documents.md`, `applications.md` |

---

## Livrables

### Backend

- Entités : Candidate, profils, documents, applications, checklist, timeline, notes
- Enums domaine : statuts, types documents, applications
- API Platform CRUD `/api/candidates`
- Controllers : timeline, documents, applications, completion, notes, upload
- Services : référence, timeline, checklist, stockage documents
- Migration `Version20260611170300`
- Seeds : `app:seed:checklist`

### Frontend

- `/candidates`, `/candidates/new`, `/candidates/:id`
- Tableau de complétude (profil, documents, financement, Campus France)
- Navigation sidebar avec `PermissionGate`

### Tests exécutés

```
PHPUnit  : OK (33 tests, 85 assertions)
PHPStan  : OK (niveau configuré)
ESLint   : OK
TypeScript : OK
Vitest   : OK (13 tests)
```

---

## Points d'attention

1. **Stockage MinIO** : implémentation locale (`var/storage/`) ; SDK S3 à brancher en production.
2. **Couverture 85%** : objectif non atteint sur le module Candidate isolé (~70%) ; tests permissions conseiller à compléter.
3. **Onglets fiche** : Parcoursup / Paris-Saclay / profils détaillés en édition à enrichir (Sprint 2.1).
4. **Déploiement DEV** : nécessite PR + merge vers `develop` pour déclencher `deploy-dev.yml`.

---

## Verdict

# SPRINT 2 NON VALIDÉ

**Motif** : couverture de tests < 85% sur le module Candidate et déploiement DEV non exécuté (branche non mergée).

**Actions pour validation** :

1. Merger `feature/sprint-2-candidate` → `develop`
2. Ajouter tests permissions conseiller + upload document API
3. Brancher MinIO SDK en environnement DEV
4. Atteindre 85% couverture PHPUnit sur `src/Entity/Candidate*`, `Infrastructure/Candidate/*`

---

## Références

- [candidate.md](./candidate.md)
- [documents.md](./documents.md)
- [applications.md](./applications.md)
- [architecture.md](./architecture.md)
- [database.md](./database.md)
