<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\PasswordPolicy;
use App\Domain\Security\SecurityEventType;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class ResetPasswordCommandHandler
{
    public function __construct(
        private PasswordResetTokenRepository $tokenRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private SecurityLogService $securityLogService,
    ) {
    }

    /** @return array<string, string> */
    public function __invoke(ResetPasswordCommand $command): array
    {
        $errors = PasswordPolicy::validate($command->password);
        if ([] !== $errors) {
            throw new BadRequestHttpException(implode(' ', $errors));
        }

        $token = $this->tokenRepository->findValidByHash(hash('sha256', $command->token));
        if (null === $token) {
            throw new BadRequestHttpException('Jeton invalide ou expiré.');
        }

        $user = $token->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));
        $token->markUsed();
        $this->userRepository->save($user);
        $this->securityLogService->log(SecurityEventType::PASSWORD_RESET_COMPLETED, $user);

        return ['message' => 'Mot de passe réinitialisé avec succès.'];
    }
}
