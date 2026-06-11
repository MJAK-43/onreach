# Gestion documentaire

## Entité `CandidateDocument`

Moteur générique pour tous les types de documents du dossier étudiant.

### Types

`passport`, `identity_card`, `photo`, `cv`, `motivation_letter`, `diploma`, `transcript`, `language_certificate`, `recommendation_letter`, `research_project`, `internship_report`, `other`

### Statuts

| Statut | Description |
|--------|-------------|
| `missing` | Non fourni |
| `uploaded` | Téléversé, en attente de validation |
| `validated` | Validé par un conseiller/admin |
| `rejected` | Rejeté (avec `rejectionReason`) |

### Champs

`filename`, `originalFilename`, `mimeType`, `size`, `storagePath`, `uploadedAt`, `validatedAt`, `validatedBy`, `rejectionReason`

## Stockage

Service : `DocumentStorageService`

- Chemin local (dev) : `var/storage/documents/candidates/{candidateId}/{documentId}.{ext}`
- Production : bucket MinIO `documents` (même structure relative)

## Upload API

```http
POST /api/candidates/{id}/documents/upload
Content-Type: multipart/form-data

file: <fichier>
type: passport
```

## Validation API

```http
POST /api/candidates/{id}/documents/{documentId}/validate
```

Enregistre l'auteur validateur et crée une entrée timeline `document.validated`.

## Lien checklist

La checklist Campus France référence des `DocumentType`. Un document `validated` marque l'item correspondant comme complété.

## Permissions

- `documents.view` — lecture
- `documents.upload` — téléversement
- `documents.validate` — validation/rejet
