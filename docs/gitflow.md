# Git Flow On'Reach

## Branches

| Branche | Rôle |
|---------|------|
| `main` | Production |
| `preprod` | Pré-production |
| `develop` | Intégration |
| `feature/*` | Nouvelles fonctionnalités |

## Flux

```
feature/* ──PR──▶ develop ──PR──▶ preprod ──PR──▶ main
```

## Règles

### `main`
- Require Pull Request
- Require Status Checks
- Block Force Push
- Restrict Direct Push
- **Jamais** de merge automatique

### `preprod`
- Require Pull Request
- Require Status Checks

### `develop`
- Déploiement automatique DEV
- Intégration des features

## Convention de nommage

| Préfixe | Usage | Merge vers |
|---------|-------|------------|
| `feature/*` | Nouvelle fonctionnalité | `develop` |
| `bugfix/*` | Correction non critique | `develop` |
| `hotfix/*` | Correction urgente production | `main` (+ cherry-pick `develop`) |
| `refactor/*` | Refactoring technique | `develop` |

Exemples :

```
feature/sprint1-candidats
bugfix/health-endpoint-cors
hotfix/jwt-expiration
refactor/health-repository
```

## Création des branches (première fois)

```bash
git checkout -b develop main
git push -u origin develop
git checkout -b preprod develop
git push -u origin preprod
```

## Protection GitHub

Configurer dans Settings → Branches :
- Secret Scanning : activé
- Dependabot : `.github/dependabot.yml`
- CodeQL : `.github/workflows/security.yml`
