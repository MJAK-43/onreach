# Sécurité — Sprint 1

## Mesures OWASP

| Menace | Mitigation |
|--------|------------|
| Brute force | Rate limiter login (email + IP) |
| Énumération utilisateurs | Réponse identique forgot-password |
| Replay token | Refresh token single-use |
| XSS | API JSON stateless, échappement frontend React |
| Injection | Doctrine ORM, validation Symfony |
| CSRF | API stateless JWT (pas de cookies session) |

## Logs de sécurité (`security_logs`)

- Connexion réussie / échouée
- Déconnexion
- MFA activé / désactivé
- Réinitialisation / changement mot de passe
- Modification rôle / permission

## Audit Trail (`audit_trails`)

Historise : utilisateur, date, action, entité, ancienne/nouvelle valeur, IP, User-Agent.

## MFA

- TOTP (Google Authenticator compatible)
- QR Code provisioning
- 8 codes de récupération à usage unique
