<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\SecurityEventType;
use App\Entity\User;
use App\Infrastructure\Security\MfaService;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class MfaEnableCommandHandler
{
    public function __construct(
        private MfaService $mfaService,
        private UserRepository $userRepository,
        private SecurityLogService $securityLogService,
    ) {
    }

    /**
     * @return array{recoveryCodes: list<string>}
     */
    public function __invoke(User $user, string $code): array
    {
        if (null === $user->getMfaSecret()) {
            throw new BadRequestHttpException('Configurez d\'abord le MFA.');
        }

        if (!$this->mfaService->verifyCode($user, $code)) {
            throw new BadRequestHttpException('Code MFA invalide.');
        }

        $user->setMfaEnabled(true);
        $this->userRepository->save($user);
        $recoveryCodes = $this->mfaService->generateRecoveryCodes($user);
        $this->securityLogService->log(SecurityEventType::MFA_ENABLED, $user);

        return ['recoveryCodes' => $recoveryCodes];
    }
}
