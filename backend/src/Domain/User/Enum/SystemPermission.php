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
    case CANDIDATES_VIEW = 'candidates.view';
    case CANDIDATES_CREATE = 'candidates.create';
    case CANDIDATES_EDIT = 'candidates.edit';
    case CANDIDATES_DELETE = 'candidates.delete';
    case CANDIDATES_NOTES = 'candidates.notes';
    case DOCUMENTS_VIEW = 'documents.view';
    case DOCUMENTS_UPLOAD = 'documents.upload';
    case DOCUMENTS_VALIDATE = 'documents.validate';
    case APPLICATIONS_VIEW = 'applications.view';
    case APPLICATIONS_EDIT = 'applications.edit';

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
            self::CANDIDATES_VIEW => 'Voir les candidats',
            self::CANDIDATES_CREATE => 'Créer des candidats',
            self::CANDIDATES_EDIT => 'Modifier les candidats',
            self::CANDIDATES_DELETE => 'Supprimer les candidats',
            self::CANDIDATES_NOTES => 'Gérer les notes candidat',
            self::DOCUMENTS_VIEW => 'Voir les documents',
            self::DOCUMENTS_UPLOAD => 'Téléverser des documents',
            self::DOCUMENTS_VALIDATE => 'Valider les documents',
            self::APPLICATIONS_VIEW => 'Voir les candidatures',
            self::APPLICATIONS_EDIT => 'Modifier les candidatures',
        };
    }
}
