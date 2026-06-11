# Candidatures (Applications)

## Modèle générique

Chaque procédure est une entité dédiée liée 1:1 au `Candidate` :

| Entité | Type | Table |
|--------|------|-------|
| `CampusFranceApplication` | `campus_france` | `campus_france_applications` |
| `ParcoursupApplication` | `parcoursup` | `parcoursup_applications` |
| `ParisSaclayApplication` | `paris_saclay` | `paris_saclay_applications` |

Extension future : ajouter une entité + enum `ApplicationType` sans dupliquer le candidat.

## Campus France

**Champs** : `studyProject`, `professionalProject`, `targetUniversities`, `targetPrograms`

**Statuts** : `draft`, `pending_documents`, `submitted`, `interview_scheduled`, `interview_passed`, `admission_obtained`, `rejected`

## Parcoursup

**Champs** : `ineNumber`, `highSchool`, `specialties`, `activities`, `interests`, `motivationProject`

**Vœux** : entité `ParcoursupWish` (relation 1:N)

## Paris-Saclay

**Champs** : `degreeLevel` (`licence`, `master`, `doctorate`), `researchProject`, `publications`, `internshipReports`

## API

```http
GET /api/candidates/{id}/applications
```

Réponse JSON agrégée :

```json
{
  "campusFrance": { "status": "...", "studyProject": "..." },
  "parcoursup": { "ineNumber": "...", "highSchool": "..." },
  "parisSaclay": { "degreeLevel": "master", "researchProject": "..." }
}
```

Les candidatures sont également exposées via les groupes de sérialisation `candidate:read` / `candidate:write` sur l'entité `Candidate`.

## Permissions

- `applications.view`
- `applications.edit`

## Checklist par procédure

`ChecklistTemplate` lié à `ApplicationType`. Seed : `app:seed:checklist`.
