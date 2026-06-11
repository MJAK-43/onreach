<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Security\SecurityEventType;
use App\Entity\SecurityLog;
use App\Entity\User;
use App\Repository\SecurityLogRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SecurityLogService
{
    public function __construct(
        private SecurityLogRepository $repository,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function log(
        SecurityEventType $event,
        ?User $user = null,
        ?string $email = null,
        ?array $metadata = null,
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        $log = new SecurityLog($event);
        $log->setUser($user);
        $log->setEmail($email ?? $user?->getEmail());
        $log->setMetadata($metadata);

        if (null !== $request) {
            $log->setIpAddress($request->getClientIp());
            $log->setUserAgent(substr((string) $request->headers->get('User-Agent', ''), 0, 500));
        }

        $this->repository->save($log);
    }
}
