<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\SecurityEventType;
use App\Entity\User;
use App\Infrastructure\Security\MfaService;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\MfaRecoveryCodeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class MfaDisableCommandHandler
{
    public function __construct(
        private MfaService $mfaService,
        private UserRepository $userRepository,
        private MfaRecoveryCodeRepository $recoveryCodeRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private SecurityLogService $securityLogService,
    ) {
    }

    /** @return array<string, string> */
    public function __invoke(User $user, string $password, string $code): array
    {
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new BadRequestHttpException('Mot de passe incorrect.');
        }

        if (!$this->mfaService->verifyCode($user, $code)) {
            throw new BadRequestHttpException('Code MFA invalide.');
        }

        $user->setMfaEnabled(false);
        $user->setMfaSecret(null);
        $this->recoveryCodeRepository->deleteForUser($user);
        $this->userRepository->save($user);
        $this->securityLogService->log(SecurityEventType::MFA_DISABLED, $user);

        return ['message' => 'MFA désactivé.'];
    }
}
