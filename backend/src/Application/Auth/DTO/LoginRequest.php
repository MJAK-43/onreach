<?php

declare(strict_types=1);

namespace App\Application\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',
        #[Assert\NotBlank]
        public string $password = '',
        public bool $rememberMe = false,
        public ?string $mfaCode = null,
    ) {
    }
}
