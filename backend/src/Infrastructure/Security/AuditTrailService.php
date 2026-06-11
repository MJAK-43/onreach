<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Entity\AuditTrail;
use App\Entity\User;
use App\Repository\AuditTrailRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class AuditTrailService
{
    public function __construct(
        private AuditTrailRepository $repository,
        private RequestStack $requestStack,
        private Security $security,
    ) {
    }

    /**
     * @param array<string, mixed>|null $oldValue
     * @param array<string, mixed>|null $newValue
     */
    public function record(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?User $actor = null,
    ): void {
        $audit = new AuditTrail($action, $entityType, $entityId, $oldValue, $newValue);
        $user = $actor ?? $this->security->getUser();
        if ($user instanceof User) {
            $audit->setUser($user);
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $audit->setIpAddress($request->getClientIp());
            $audit->setUserAgent(substr((string) $request->headers->get('User-Agent', ''), 0, 500));
        }

        $this->repository->save($audit);
    }
}
