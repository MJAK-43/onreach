# Module Candidate — Dossier Étudiant Unifié

## Objectif

Agrégat central `Candidate` regroupant identité, profils, documents, candidatures et historique, sans duplication entre procédures (Campus France, Parcoursup, Paris-Saclay).

## Agrégat

```
Candidate
├── Identity (champs principaux)
├── AcademicProfile + AcademicRecord[]
├── LanguageProfile + LanguageCertificate[]
├── ProfessionalProfile + ProfessionalExperience[]
├── FinancingProfile + Guarantor
├── CandidateDocument[]
├── CampusFranceApplication | ParcoursupApplication | ParisSaclayApplication
├── CandidateNote[]
├── CandidateTimelineEntry[]
└── AssignedCounselor (User)
```

## Statuts globaux

| Code | Label |
|------|-------|
| `lead` | Lead |
| `profile_incomplete` | Profil incomplet |
| `documents_pending` | Documents en attente |
| `in_progress` | En cours |
| `admission_obtained` | Admission obtenue |
| `visa_obtained` | Visa obtenu |
| `completed` | Clôturé |
| `suspended` | Suspendu |
| `cancelled` | Annulé |

## API REST

| Méthode | Endpoint | Permission |
|---------|----------|------------|
| GET | `/api/candidates` | `candidates.view` |
| GET | `/api/candidates/{id}` | `candidates.view` |
| POST | `/api/candidates` | `candidates.create` |
| PUT | `/api/candidates/{id}` | `candidates.edit` |
| DELETE | `/api/candidates/{id}` | `candidates.delete` |
| GET | `/api/candidates/{id}/timeline` | `candidates.view` |
| GET | `/api/candidates/{id}/documents` | `documents.view` |
| POST | `/api/candidates/{id}/documents/upload` | `documents.upload` |
| POST | `/api/candidates/{id}/documents/{docId}/validate` | `documents.validate` |
| GET | `/api/candidates/{id}/applications` | `applications.view` |
| GET | `/api/candidates/{id}/completion` | `candidates.view` |
| GET/POST | `/api/candidates/{id}/notes` | `candidates.notes` |

## Filtres (SearchFilter)

- `status`, `nationality`, `country`, `assignedCounselor.id`

## RBAC

| Rôle | Accès |
|------|-------|
| SUPER_ADMIN / ADMIN | Tous les candidats |
| COUNSELOR | Candidats assignés uniquement |
| CANDIDATE | Son propre dossier (email) |

## Référence dossier

Format généré automatiquement : `ONR-{année}-{séquence}` via `CandidateReferenceGenerator`.

## Frontend

- `/candidates` — liste
- `/candidates/new` — création
- `/candidates/:id` — fiche (onglets : infos, documents, Campus France, historique, notes)

## Seed

```bash
php bin/console app:seed:checklist
```

Crée le template checklist Campus France (`campus_france`).
