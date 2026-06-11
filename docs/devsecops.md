# DevSecOps

## Principes

1. **Secrets Management** — variables d'environnement uniquement
2. **HTTPS Ready** — Traefik avec entrypoint TLS
3. **Shift Left** — tests et scans dans la CI

## Interdictions

- Secrets dans le code source
- Mots de passe hardcodés
- Désactivation des pipelines de sécurité
- Suppression des tests pour faire passer la CI

## Outils

| Outil | Usage |
|-------|-------|
| `composer audit` | Vulnérabilités PHP |
| `npm audit` | Vulnérabilités npm |
| CodeQL | Analyse statique sécurité |
| Dependabot | Mises à jour automatiques |
| Secret Scanning | Détection de secrets (GitHub) |

## Variables sensibles

Voir `.env.example` pour la liste complète.

Générer `APP_SECRET` :
```bash
php -r "echo bin2hex(random_bytes(32));"
```

## JWT

Les clés RSA sont générées au démarrage du conteneur backend (`docker/entrypoint.sh`).
Ne pas committer `config/jwt/*.pem`.
