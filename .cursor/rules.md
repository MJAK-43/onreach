# On'Reach — Cursor Rules

## Architecture

- Respecter **SOLID**, **DDD**, **CQRS**
- Utiliser **Repository Pattern**, **Service Pattern**, **Factory Pattern**, **Adapter Pattern**
- Architecture en couches : `Domain/`, `Application/`, `Infrastructure/`, `Presentation/`
- Event Driven pour les interactions inter-modules

## Backend (Symfony)

- Aucune logique métier dans les controllers
- Aucun SQL dans les controllers
- Controllers = couche Presentation uniquement
- Use cases dans `Application/`
- Interfaces dans `Domain/`, implémentations dans `Infrastructure/`

## Frontend (React)

- Composants UI réutilisables dans `components/`
- Logique API dans `lib/api.ts` et TanStack Query
- Pas de logique métier dans les pages Sprint 0+

## Sécurité

- **Interdit** : secrets dans le code, mots de passe hardcodés
- Utiliser les variables d'environnement via `.env` (non commité)
- Sanitize inputs, escape outputs
- JWT pour l'authentification API

## Qualité

- **Interdit** : suppression des tests, désactivation des pipelines
- Maintenir PHPUnit, PHPStan, ESLint, Vitest
- Le pipeline CI doit échouer en cas d'erreur

## Sprint 0

- Ne pas implémenter de modules métier (candidats, paiements, visas, RAG, agent IA)
- Étendre les fondations existantes
