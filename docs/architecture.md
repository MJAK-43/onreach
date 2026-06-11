# Architecture On'Reach

## Vue d'ensemble

On'Reach est une plateforme SaaS modulaire pour l'accompagnement des étudiants internationaux.

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Frontend   │────▶│   Backend   │────▶│  PostgreSQL │
│  React 19   │     │  Symfony 7  │     │  + pgvector │
└─────────────┘     └──────┬──────┘     └─────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        ▼                  ▼                  ▼
   ┌─────────┐       ┌─────────┐       ┌─────────┐
   │  Redis  │       │  MinIO  │       │AI Service│
   └─────────┘       └─────────┘       └─────────┘
```

## Backend — DDD / CQRS

| Couche | Responsabilité |
|--------|----------------|
| `Domain/` | Entités, Value Objects, interfaces Repository |
| `Application/` | Commands, Queries, Handlers (CQRS) |
| `Infrastructure/` | Doctrine, Redis, adapters externes |
| `Presentation/` | Controllers, API Platform resources |

## Services

| Service | URL locale | Port |
|---------|-----------|------|
| Frontend | http://frontend.localhost | 80 |
| API | http://api.localhost | 80 |
| AI | http://ai.localhost | 80 |
| MinIO | http://minio.localhost | 9001 |
| Grafana | http://localhost:3000 | 3000 |
| Traefik | http://localhost:8080 | 8080 |

## Observabilité

- **OpenTelemetry** : collecteur OTLP
- **Prometheus** : métriques
- **Loki** : logs
- **Grafana** : dashboards

## Patterns

- Repository Pattern pour l'accès aux données
- Adapter Pattern pour les intégrations (OpenAI, Brevo, WhatsApp)
- Event Driven via Symfony Messenger
- Workflow Symfony pour les processus d'admission

## Sprint 2 — Module Candidate

### Agrégat principal

`Candidate` (Doctrine + API Platform) avec sous-ressources via controllers dédiés :

- `CandidateProvider` / `CandidateProcessor` — filtrage RBAC, génération référence, timeline
- `CandidateSubresourceController` — timeline, documents, applications, complétude, notes
- `CandidateDocumentController` — upload et validation
- `ChecklistService` — calcul de complétude par procédure
- `DocumentStorageService` — stockage fichiers (local / MinIO)

### Permissions ajoutées

`candidates.*`, `documents.*`, `applications.*` — voir `docs/candidate.md` et `docs/rbac.md`.

### Frontend Sprint 2

Pages React : liste, création, fiche candidat avec tableau de complétude et onglets.
