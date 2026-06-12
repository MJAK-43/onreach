# Portail candidat — Mes démarches

## Vue d'ensemble

Le module **Mes démarches** permet au candidat de suivre Campus France, Parcoursup et Paris-Saclay depuis une interface unifiée.

## Routes frontend

| Route | Page |
|-------|------|
| `/demarches` | Vue globale (KPIs, alertes, cartes procédures) |
| `/demarches/campus-france` | Détail Campus France |
| `/demarches/parcoursup` | Détail Parcoursup + vœux |
| `/demarches/paris-saclay` | Détail Paris-Saclay |
| `/demarches/historique` | Timeline globale |

## API `/api/me/*`

Réservée au rôle **CANDIDATE** (résolution dossier par email).

| Endpoint | Permission |
|----------|------------|
| `GET /api/me/applications` | `applications.view` |
| `GET /api/me/campus-france` | `applications.view` |
| `GET /api/me/parcoursup` | `applications.view` |
| `GET /api/me/paris-saclay` | `applications.view` |
| `GET /api/me/documents` | `documents.view` |
| `GET /api/me/timeline` | `candidates.view` |

## Sécurité

- `CandidateResolver` : vérifie le rôle CANDIDATE et la liaison email ↔ dossier.
- Aucun `candidateId` dans l'URL : le candidat ne voit que ses données.
- Staff utilise les routes `/api/candidates/{id}/*` existantes.

## Extensions futures

- Messagerie temps réel (structure `messages.readOnly` en place)
- Upload document depuis les pages démarches
- Paiements, logement, Agent IA (modules séparés)
