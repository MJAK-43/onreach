# Audit — Module de suivi des candidatures On'Reach

**Date :** 18 juin 2026  
**Statut :** Analyse préalable — **aucune implémentation démarrée**  
**Périmètre :** Sprint refonte suivi candidatures (Campus France, Parcoursup, Paris-Saclay)

---

## 1. Résumé exécutif

L'application dispose déjà d'un **socle MVP** orienté portail candidat : trois entités d'application (CF / Parcoursup / Paris-Saclay), statuts enum, checklist documentaire Campus France, timeline événementielle et pages `/demarches` côté candidat.

En revanche, le cahier des charges demande un **moteur de parcours configurable** (grandes étapes + sous-étapes persistées, campagnes, double validation, notifications, paramètres admin). **~70 % du périmètre reste à construire** ; le reste peut être **étendu** plutôt que remplacé.

**Recommandation :** approche incrémentale en 4 phases, sans régression sur Mon Dossier ni les API `/api/me/*` existantes.

---

## 2. Inventaire de l'existant

### 2.1 Backend — Données & domaine

| Élément | Existe | Fichiers clés |
|---------|--------|---------------|
| Entité `Candidate` (profil complet) | ✅ | `Entity/Candidate.php` |
| `CampusFranceApplication` + statut enum | ✅ | 10 statuts, pas de sous-étapes |
| `ParcoursupApplication` + vœux | ✅ | Statut par vœu uniquement |
| `ParisSaclayApplication` + statut enum | ✅ | 9 statuts |
| Workflows CF / PS (10 / 8 étapes) | ⚠️ Hardcodés | `CandidatePortalService.php` |
| Workflow Parcoursup par étapes | ❌ | Progression = vœux uniquement |
| Sous-étapes persistées | ❌ | — |
| Type candidature (1ère année / poursuite) | ❌ | — |
| Attribution auto parcours | ❌ | Seed manuel dans demo-users |
| `ChecklistTemplate` / `ChecklistItem` | ✅ | Campus France seulement (6 docs) |
| `CandidateTimelineEntry` | ✅ | Actions libres, non liées aux étapes |
| `AuditTrail` (User/Role/Permission) | ✅ | Pas lié aux parcours candidat |
| Permission `applications.edit` | ⚠️ Déclarée | Aucun endpoint |
| Campagnes (2026, 2027…) | ❌ | — |
| Notifications in-app | ❌ | — |
| Messagerie / mini-chat | ❌ | Placeholders frontend |
| Paramètres > Parcours | ❌ | — |
| Double validation | ❌ | — |
| Import calendrier IA | ❌ | Service AI squelette seulement |

### 2.2 Backend — API

| Endpoint | Rôle | État |
|----------|------|------|
| `GET /api/me/applications` | Vue globale candidat | ✅ |
| `GET /api/me/campus-france` | Détail + workflow calculé | ✅ |
| `GET /api/me/parcoursup` | Vœux + infos | ✅ |
| `GET /api/me/paris-saclay` | Détail + workflow | ✅ |
| `GET /api/me/timeline` | Historique | ✅ |
| `GET /api/candidates/{id}/applications` | Staff — agrégation | ✅ |
| `PUT /api/candidates/{id}` | Màj nested applications | ⚠️ Peu utilisé, pas de validation transitions |
| PATCH sous-étapes / statuts parcours | — | ❌ |
| CRUD parcours (admin settings) | — | ❌ |
| Notifications API | — | ❌ |

### 2.3 Frontend — Par rôle

#### Candidat

| Rubrique | Route | État |
|----------|-------|------|
| Dashboard | `/` | ⚠️ Mix API + données demo (RDV, paiements, tâches) |
| Mes démarches | `/demarches/*` | ✅ Branché API, stepper horizontal basique |
| Mon dossier | `/my-file/*` | ✅ Complet (13 sections) |
| Mes candidatures (spec) | — | ❌ Route `/demarches` à renommer/enrichir |
| Procedure | `/procedure` | ⚠️ Timeline demo statique |
| Notifications | Header | ❌ Badge « 3 » statique |
| Chat | Onglets Messages | ❌ « Bientôt disponible » |

#### Conseiller / Admin

| Rubrique | Route | État |
|----------|-------|------|
| Dashboard staff | `/` | ✅ KPIs, table suivi basique |
| Liste candidats | `/candidates` | ✅ |
| Fiche candidat | `/candidates/:id` | ⚠️ Info, docs, CF checklist, timeline, notes — **pas Parcoursup/PS** |
| Suivi des démarches (spec) | — | ❌ Table filtres/colonnes absente |
| Paramètres > Parcours | `/settings` | ❌ Stub Sprint 0 |
| Rapports admin | `/admin/reports` | ❌ Stub |

### 2.4 Composants UI réutilisables

| Composant | Usage actuel | Réutilisable refonte |
|-----------|--------------|----------------------|
| `WorkflowStepper` | CF, Paris-Saclay | ⚠️ À remplacer par timeline premium |
| `TimelineList` | Historique | ✅ Base timeline |
| `GlobalCompletionHero` | Overview démarches | ✅ Anneau progression |
| `CandidateTrackingTable` | Dashboard staff | ⚠️ Colonnes insuffisantes |
| `CompletionPanel` | Mon dossier | ✅ |
| `CandidateDetailPage` onglets | Staff | ✅ Structure à étendre |
| `DemarchesLayout` | Nav secondaire | ✅ Pattern sidebar |

---

## 3. Écart cahier des charges vs existant

### 3.1 Type de candidature & attribution parcours

| Exigence | Écart |
|----------|-------|
| Champ obligatoire « Première année » / « Poursuite » | **Absent** sur `Candidate` |
| Auto-création Parcoursup + CF (1ère année) | **Absent** — seed manuel |
| Auto-création CF + Paris-Saclay (poursuite) | **Absent** |

### 3.2 Structure parcours (spec vs code)

**Campus France — spec : 7 grandes étapes, ~35 sous-étapes**  
**Existant : 10 étapes plates** (sans sous-étapes), dérivées du statut enum :

```
dossier_created → documents_received → … → departure
```

**Parcoursup — spec : 5 grandes étapes, ~15 sous-étapes**  
**Existant : pas de workflow** ; vœux avec statut `ParcoursupWishStatus`.

**Paris-Saclay — spec : 6 grandes étapes, ~18 sous-étapes**  
**Existant : 8 étapes plates**, statut enum `ParisSaclayStatus`.

### 3.3 Fonctionnalités transverses

| Fonctionnalité | Spec | Existant |
|----------------|------|----------|
| Cocher/décocher sous-étapes (staff) | ✅ | ❌ |
| Progression auto (étape + parcours) | ✅ | ⚠️ Partielle (checklist docs CF) |
| Double validation | ✅ | ❌ |
| Statuts Bloqué + commentaire obligatoire | ✅ | ❌ (enum candidat seulement) |
| Notifications (étape, parcours, relances) | ✅ | ❌ |
| Audit parcours (validation, statut, conseiller) | ✅ | ⚠️ Timeline générique |
| Campagnes + échéances | ✅ | ❌ |
| Paramètres admin configurables | ✅ | ❌ |
| Archivage logique | ✅ | ❌ |
| Import calendrier IA | ✅ | ❌ |
| UX premium (Linear/Notion…) | ✅ | ⚠️ Stepper horizontal classique |

---

## 4. Analyse des risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Régression Mon Dossier / `/api/me/profile` | Moyenne | Critique | Tests non-régression, branche feature, pas de toucher `CandidateProfileService` |
| Migration données demo (CF statut → sous-étapes) | Haute | Moyen | Script migration idempotent + mapping statut → étape courante |
| Duplication checklist docs vs sous-étapes | Moyenne | Moyen | Lier sous-étapes « document » au `DocumentType` existant |
| Performance (N+1 sur table staff) | Moyenne | Moyen | Vues SQL / DTO agrégés, pagination, index |
| Scope creep (IA, chat, notifications) | Haute | Élevé | Phaser : MVP parcours → staff → notifs → IA |
| Conflit `/demarches` vs « Mes candidatures » | Faible | Faible | Renommer nav, garder routes |
| Permissions RBAC incomplètes | Moyenne | Moyen | Nouvelles permissions `pathways.*`, tests dédiés |

---

## 5. Proposition UX/UI (direction 2026)

### 5.1 Principes visuels

- **Palette :** slate/zinc neutre + accent bleu indigo (cohérent sidebar actuelle)
- **Surfaces :** cartes `rounded-2xl`, fond `bg-white/80 backdrop-blur-sm`, ombre `shadow-sm`
- **Typographie :** titres `tracking-tight font-semibold`, métadonnées `text-sm text-muted-foreground`
- **Motion :** transitions 200–300 ms sur hover/progression ; Framer Motion pour expand/collapse étapes
- **Interdit :** stepper horizontal Bootstrap, bordures épaisses, couleurs saturées

### 5.2 Patterns par écran

#### Candidat — « Mes candidatures » (ex-`/demarches`)

```
┌─────────────────────────────────────────────────────────┐
│  Hero : complétude globale (anneau) + campagne 2026      │
├──────────────┬──────────────────────────────────────────┤
│ Nav parcours │  Carte parcours active                    │
│ • Campus FR  │  ┌─ Timeline verticale (grandes étapes)   │
│ • Parcoursup │  │  ▼ Étape 3 — Dépôt (62%)               │
│ • Saclay     │  │    ○ Sous-étape 1 ✓                     │
│              │  │    ○ Sous-étape 2 ⏳ (échéance 15/07)  │
│              │  └─ Statut : En cours                     │
└──────────────┴──────────────────────────────────────────┘
```

- **Lecture seule** pour le candidat
- Sous-étapes : icône statut + label + date limite + badge double validation (si activée)

#### Conseiller / Admin — « Suivi des démarches »

- **Table type Attio** : lignes denses, hover reveal actions
- Colonnes spec + tri/filtres sticky header
- Clic ligne → drawer latéral (fiche rapide) ou `/candidates/:id#parcours`
- Checkbox sous-étapes inline avec confirmation + audit

#### Fiche candidat — onglet « Parcours »

- Remplacer/compléter onglet « campus » unique
- Accordion 3 parcours avec timeline + actions staff
- Panneau « Documents » reste lié à Mon Dossier (pas de duplication upload)

#### Double validation (si activée)

```
Sous-étape : Paiement Campus France
  Conseiller    [✓ Validée]     Marie K. — 12/06
  Administrateur [⏳ En attente]
  → Non comptée dans la progression
```

### 5.3 Maquettes fil de wireframe (mobile-first)

**Écran progression parcours (candidat)**

1. Header sticky : nom parcours + % + statut pill  
2. Scroll vertical : cartes étape (collapsed) → tap expand sous-étapes  
3. Footer : prochaine échéance + lien conseiller  

**Écran suivi staff**

1. Barre filtres (chips : Parcours, Statut, Campagne, Conseiller)  
2. Table responsive → cards sur mobile  
3. Bulk actions désactivées en V1  

---

## 6. Architecture technique proposée

### 6.1 Nouveau modèle de domaine (BDD)

```
Campaign (2026, 2027…)
  └── PathwayTemplate (campus_france, parcoursup, paris_saclay…)
        └── StageTemplate (grande étape : ordre, titre, description)
              └── SubStepTemplate (ordre, obligatoire, échéance relative campagne)

Candidate
  ├── applicationType: FIRST_YEAR | CONTINUING  (NOUVEAU)
  └── CandidatePathway (instance par candidat + campagne)
        ├── status: NOT_STARTED | IN_PROGRESS | BLOCKED | …
        ├── assignedCounselor (hérité ou snapshot)
        └── CandidateStage (instance)
              └── CandidateSubStep (instance)
                    ├── status: pending | validated
                    ├── counselorValidatedAt / by
                    ├── adminValidatedAt / by  (si double validation)
                    ├── dueDate
                    ├── blockedReason (si BLOCKED)
                    └── archivedAt (soft delete)
```

**Tables audit :** `pathway_audit_log` (action, old/new JSON, user, role, timestamp)

**Settings :** `pathway_settings` (double_validation_enabled, …)

### 6.2 Réutilisation maximale

| Existant | Réutilisation |
|----------|---------------|
| `CampusFranceApplication.status` | Migrer → position dans parcours ; garder colonne en sync ou deprecated |
| `ChecklistItem` / documents | Sous-étapes type `document` référencent `DocumentType` |
| `CandidateTimelineService` | Émettre events `pathway.substep.validated`, etc. |
| `CandidatePortalService` | Refactor → `PathwayProgressService` consomme BDD |
| `MeApplicationsController` | Adapter réponses, compat backward 1 version |
| `DemarchesComponents` | Extraire design system parcours |
| `CandidateDetailPage` | Nouvel onglet Parcours |
| `AuditTrailService` | Étendre ou parallèle pour parcours |

### 6.3 API cible (extrait)

| Méthode | Route | Rôle |
|---------|-------|------|
| GET | `/api/me/pathways` | Candidat — lecture |
| GET | `/api/pathways/candidates` | Staff — liste suivi (filtres) |
| GET | `/api/candidates/{id}/pathways` | Détail parcours candidat |
| PATCH | `/api/candidates/{id}/pathways/{pathwayId}/substeps/{id}` | Valider / invalider / bloquer |
| GET/POST/PATCH | `/api/admin/pathway-templates` | Paramètres parcours |
| GET/PATCH | `/api/admin/pathway-settings` | Double validation, campagne active |
| POST | `/api/admin/pathways/import-calendar` | IA (phase 4) |

### 6.4 Migrations & compatibilité

1. Créer tables template + instance  
2. Seed campagne 2026 + 3 parcours spec complets  
3. Migration : candidats demo → `CandidatePathway` depuis statuts actuels  
4. Conserver API `/api/me/applications` avec shape enrichi (champs legacy deprecated)  
5. Rollback : templates versionnés, instances non supprimées physiquement  

---

## 7. Plan de livraison recommandé

### Phase 1 — Fondations (2–3 sem.) — **P0**

- [ ] Modèle BDD + migrations + seeds campagne 2026  
- [ ] Type candidature + attribution auto parcours  
- [ ] `PathwayProgressService` + recalcul progression  
- [ ] API lecture candidat + staff  
- [ ] Tests permissions + non-régression  

### Phase 2 — UI premium candidat + staff (2–3 sem.) — **P0**

- [ ] Refonte « Mes candidatures » (timeline verticale)  
- [ ] Page « Suivi des démarches » (table + filtres)  
- [ ] Onglet Parcours fiche candidat  
- [ ] Validation sous-étapes (simple)  

### Phase 3 — Transverse (1–2 sem.) — **P1**

- [ ] Double validation + paramètres admin  
- [ ] Statut Bloqué + commentaire  
- [ ] Audit parcours complet  
- [ ] Notifications in-app (Symfony Notifier ou table dédiée)  
- [ ] Relances échéances (Messenger scheduled)  

### Phase 4 — Avancé (2+ sem.) — **P2**

- [ ] Paramètres CRUD parcours (admin UI)  
- [ ] Campagnes 2027/2028  
- [ ] Import calendrier IA  
- [ ] Mini-chat (si spec confirmée)  
- [ ] Dashboard stats enrichis  

---

## 8. Éléments à ne pas dupliquer

| Déjà en place | Action |
|---------------|--------|
| Mon Dossier (`/my-file/*`, `profile-api`) | **Ne pas refaire** — lier sous-étapes documents |
| Upload / validation docs | Réutiliser `MeDocumentsController` + staff validate |
| RBAC / rôles | Étendre permissions, pas nouveau système |
| Rendez-vous | Lier sous-étapes « entretien » aux RDV existants (phase 3) |
| `CandidateApplicationsPage.tsx` | **Supprimer** ou fusionner (orpheline) |
| Workflows hardcodés `CandidatePortalService` | **Remplacer** progressivement, puis retirer constantes |

---

## 9. Tests exigés (plan)

- PHPUnit : progression calcul, double validation, permissions PATCH substep  
- PHPUnit : attribution auto parcours à la création candidat  
- PHPUnit : migration statuts legacy → instances  
- Vitest : composants timeline, table staff filtres  
- E2E manuel : candidat lecture seule, conseiller coche, admin double val  

---

## 10. Liste des améliorations recommandées (post-MVP)

1. Sync API officielles Campus France / Parcoursup (webhook)  
2. Portail candidat : export PDF progression  
3. Tableau Kanban alternatif au table view staff  
4. Webhooks n8n sur changement statut parcours  
5. Couverture PHPStan level 6 sur nouveau module  
6. Branch protection + `quality.ps1` avant merge (déjà en place CI)  

---

## 11. Décision requise avant développement

1. **Renommer** `/demarches` → `/my-applications` (« Mes candidatures ») ou garder URL ?  
2. **Chat** : MVP messagerie ou report phase 4 ?  
3. **Notifications** : in-app seulement ou email (Brevo) dès phase 3 ?  
4. **Priorité phase 1** : valider le modèle BDD ci-dessus  

---

**Verdict :** le projet est **prêt pour la phase de conception détaillée + Phase 1 backend**, sans casser l'existant. Aucun code métier parcours configurable n'est en place aujourd'hui ; la refonte est **majoritairement additive**.
