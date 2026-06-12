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
                SystemPermission::CANDIDATES_VIEW,
                SystemPermission::CANDIDATES_CREATE,
                SystemPermission::CANDIDATES_EDIT,
                SystemPermission::CANDIDATES_DELETE,
                SystemPermission::CANDIDATES_NOTES,
                SystemPermission::DOCUMENTS_VIEW,
                SystemPermission::DOCUMENTS_UPLOAD,
                SystemPermission::DOCUMENTS_VALIDATE,
                SystemPermission::APPLICATIONS_VIEW,
                SystemPermission::APPLICATIONS_EDIT,
                SystemPermission::APPOINTMENTS_VIEW,
                SystemPermission::APPOINTMENTS_MANAGE,
            ],
            self::COUNSELOR => [
                SystemPermission::CANDIDATES_VIEW,
                SystemPermission::CANDIDATES_CREATE,
                SystemPermission::CANDIDATES_EDIT,
                SystemPermission::CANDIDATES_NOTES,
                SystemPermission::DOCUMENTS_VIEW,
                SystemPermission::DOCUMENTS_UPLOAD,
                SystemPermission::DOCUMENTS_VALIDATE,
                SystemPermission::APPLICATIONS_VIEW,
                SystemPermission::APPLICATIONS_EDIT,
                SystemPermission::APPOINTMENTS_VIEW,
                SystemPermission::APPOINTMENTS_MANAGE,
            ],
            self::ACCOUNTANT, self::HOUSING_MANAGER => [
                SystemPermission::USERS_VIEW,
            ],
            self::CANDIDATE => [
                SystemPermission::CANDIDATES_VIEW,
                SystemPermission::PROFILE_EDIT,
                SystemPermission::DOCUMENTS_VIEW,
                SystemPermission::DOCUMENTS_UPLOAD,
                SystemPermission::APPLICATIONS_VIEW,
                SystemPermission::APPOINTMENTS_VIEW,
                SystemPermission::APPOINTMENTS_BOOK,
            ],
        };
    }
}
