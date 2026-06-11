<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Entity\User;

final readonly class ChangePasswordCommand
{
    public function __construct(
        public User $user,
        public string $currentPassword,
        public string $newPassword,
    ) {
    }
}
