<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class LoginRateLimiter
{
    public function __construct(
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $loginIpLimiter,
    ) {
    }

    public function consume(string $email, ?string $ip): bool
    {
        $emailLimiter = $this->loginLimiter->create(strtolower($email));
        if (!$emailLimiter->consume(1)->isAccepted()) {
            return false;
        }

        if (null !== $ip) {
            $ipLimiter = $this->loginIpLimiter->create($ip);
            if (!$ipLimiter->consume(1)->isAccepted()) {
                return false;
            }
        }

        return true;
    }
}
