<?php

declare(strict_types=1);

namespace App\Domain\Security;

enum SecurityEventType: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILED = 'login_failed';
    case LOGOUT = 'logout';
    case MFA_ENABLED = 'mfa_enabled';
    case MFA_DISABLED = 'mfa_disabled';
    case PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    case PASSWORD_RESET_COMPLETED = 'password_reset_completed';
    case PASSWORD_CHANGED = 'password_changed';
    case ROLE_UPDATED = 'role_updated';
    case PERMISSION_UPDATED = 'permission_updated';
}
