<?php

declare(strict_types=1);

namespace App\Domain\User\Enum;

enum SystemRole: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case COUNSELOR = 'COUNSELOR';
    case ACCOUNTANT = 'ACCOUNTANT';
    case HOUSING_MANAGER = 'HOUSING_MANAGER';
    case CANDIDATE = 'CANDIDATE';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::ADMIN => 'Administrateur',
            self::COUNSELOR => 'Conseiller',
            self::ACCOUNTANT => 'Comptable',
            self::HOUSING_MANAGER => 'Gestionnaire logement',
            self::CANDIDATE => 'Candidat',
        };
    }

    /**
     * @return list<SystemPermission>
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => SystemPermission::cases(),
            self::ADMIN => [
                SystemPermission::USERS_VIEW,
                SystemPermission::USERS_CREATE,
                SystemPermission::USERS_EDIT,
                SystemPermission::USERS_DELETE,
                SystemPermission::ROLES_VIEW,
                SystemPermission::ROLES_EDIT,
                SystemPermission::PERMISSIONS_VIEW,
                SystemPermission::PERMISSIONS_EDIT,
                SystemPermission::SYSTEM_LOGS,
            ],
            self::COUNSELOR, self::ACCOUNTANT, self::HOUSING_MANAGER => [
                SystemPermission::USERS_VIEW,
            ],
            self::CANDIDATE => [],
        };
    }
}
