<?php

declare(strict_types=1);

namespace App\Domain\User\Enum;

enum SystemPermission: string
{
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';
    case ROLES_VIEW = 'roles.view';
    case ROLES_EDIT = 'roles.edit';
    case PERMISSIONS_VIEW = 'permissions.view';
    case PERMISSIONS_EDIT = 'permissions.edit';
    case SYSTEM_LOGS = 'system.logs';
    case SYSTEM_ARCHITECTURE = 'system.architecture';

    public function label(): string
    {
        return match ($this) {
            self::USERS_VIEW => 'Voir les utilisateurs',
            self::USERS_CREATE => 'Créer des utilisateurs',
            self::USERS_EDIT => 'Modifier les utilisateurs',
            self::USERS_DELETE => 'Supprimer les utilisateurs',
            self::ROLES_VIEW => 'Voir les rôles',
            self::ROLES_EDIT => 'Modifier les rôles',
            self::PERMISSIONS_VIEW => 'Voir les permissions',
            self::PERMISSIONS_EDIT => 'Modifier les permissions',
            self::SYSTEM_LOGS => 'Consulter les logs système',
            self::SYSTEM_ARCHITECTURE => 'Consulter l\'architecture',
        };
    }
}
