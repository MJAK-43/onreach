# Suivi des candidatures

## Entités

| Entité | Description |
|--------|-------------|
| `CampusFranceApplication` | Statut workflow Campus France |
| `ParcoursupApplication` | Dossier Parcoursup |
| `ParcoursupWish` | Vœux (formation, université, statut) |
| `ParisSaclayApplication` | Candidature Paris-Saclay + niveau |

## Statuts Campus France

`DRAFT` → `PENDING_DOCUMENTS` → `SUBMITTED` → `INTERVIEW_SCHEDULED` → `INTERVIEW_PASSED` → `ADMISSION_OBTAINED` → `VISA_PENDING` → `VISA_OBTAINED` → `COMPLETED`

## Statuts vœux Parcoursup

`BROUILLON`, `SOUMIS`, `EN_ANALYSE`, `ACCEPTE`, `LISTE_ATTENTE`, `REFUSE`

## Statuts Paris-Saclay

`DRAFT` → `PENDING_DOCUMENTS` → `SUBMITTED` → `UNDER_REVIEW` → `ADMISSION_OBTAINED` → `VISA_PENDING` → `VISA_OBTAINED` → `COMPLETED`

## Service

`CandidatePortalService` agrège complétude, workflows, documents, alertes et timeline pour le portail candidat.

## Indicateurs documents

| Couleur | Statut API |
|---------|------------|
| Vert | `validated` |
| Orange | `pending` |
| Rouge | `missing`, `rejected` |
