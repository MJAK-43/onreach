# Gestion documentaire — Mon dossier

## Endpoints candidat

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/me/documents` | Liste des documents |
| POST | `/api/me/documents` | Upload (multipart) |
| PUT | `/api/me/documents/{id}` | Remplacement |
| DELETE | `/api/me/documents/{id}` | Suppression |
| GET | `/api/me/documents/{id}/download` | Téléchargement |
| GET | `/api/me/documents/{id}/preview` | Prévisualisation |

## Types supportés

`passport`, `photo`, `cv`, `motivation_letter`, `diploma`, `transcript`, `tcf`, `delf`, `dalf`, `toefl`, `ielts`, `recommendation_letter`, `support_attestation`, `other`

## Statuts

- `missing` — aucun fichier
- `uploaded` — téléversé, en attente
- `validated` — validé par le conseiller
- `rejected` — refusé (motif obligatoire)

## Validation conseiller

`POST /api/candidates/{id}/documents/{documentId}/validate`  
`POST /api/candidates/{id}/documents/{documentId}/reject` (body: `{ "reason": "..." }`)

## Versionning

Chaque remplacement incrémente `version` et conserve l'historique dans la timeline.
