# Profil candidat — Mon dossier

## Endpoints

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/me/profile` | Lecture du dossier complet |
| PUT | `/api/me/profile` | Mise à jour partielle |
| GET | `/api/me/completion` | Indicateurs de complétude |
| GET | `/api/me/history` | Timeline du dossier |

## Sections

- `personal` — identité, passeport, CNI
- `contact` — adresse, téléphones, emails
- `studyProject` — projet d'études
- `careerProject` — projet professionnel
- `academic` — parcours et `records[]`
- `languages` — niveaux et `certificates[]`
- `financing` — financement et `guarantors[]`
- `experiences` — expériences professionnelles

## Sécurité

- Rôle `ROLE_CANDIDATE` uniquement
- Permission `profile.edit` requise pour PUT
- Validation serveur Symfony sur chaque section

## Auto-save frontend

Sauvegarde automatique toutes les 5 secondes via `useAutoSave` (statuts : pending, saving, saved, error).
