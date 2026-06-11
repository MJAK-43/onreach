# Authentification — Sprint 1

## Endpoints

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/api/auth/login` | Connexion (email, password, rememberMe?, mfaCode?) |
| POST | `/api/auth/logout` | Déconnexion (invalidation refresh token) |
| POST | `/api/auth/refresh` | Renouvellement JWT |
| GET | `/api/me` | Profil utilisateur courant |
| POST | `/api/auth/forgot-password` | Demande réinitialisation |
| POST | `/api/auth/reset-password` | Réinitialisation mot de passe |
| POST | `/api/auth/change-password` | Changement mot de passe (authentifié) |
| POST | `/api/auth/mfa/setup` | Génération secret TOTP + QR |
| POST | `/api/auth/mfa/enable` | Activation MFA |
| POST | `/api/auth/mfa/disable` | Désactivation MFA |

## JWT

- Algorithme **RS256** (Lexik JWT Authentication Bundle)
- Refresh token via **Gesdinet JWT Refresh Token Bundle**
- Remember Me : TTL refresh token étendu (90 jours)

## Politique mot de passe

- 12 caractères minimum
- Majuscule, minuscule, chiffre, caractère spécial

## Seed

```bash
php bin/console app:seed:rbac
```

Compte par défaut : `admin@onreach.inovixora.fr` / `Admin@OnReach12!`
