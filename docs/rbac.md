# RBAC — Sprint 1

## Rôles système

| Code | Label |
|------|-------|
| SUPER_ADMIN | Super administrateur |
| ADMIN | Administrateur |
| COUNSELOR | Conseiller |
| ACCOUNTANT | Comptable |
| HOUSING_MANAGER | Gestionnaire logement |
| CANDIDATE | Candidat |

## Permissions système

- `users.view`, `users.create`, `users.edit`, `users.delete`
- `roles.view`, `roles.edit`
- `permissions.view`, `permissions.edit`
- `system.logs`, `system.architecture`

## Modèle

```
User ←→ Role ←→ Permission
```

- Voter `PermissionVoter` : vérifie les permissions via `is_granted('users.view')`
- SUPER_ADMIN : accès total

## API Platform

- `GET/PATCH /api/roles`
- `GET/PATCH /api/permissions`
- `GET/POST/PATCH/DELETE /api/users`
