<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\PasswordPolicy;
use App\Domain\Security\SecurityEventType;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class ChangePasswordCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private SecurityLogService $securityLogService,
    ) {
    }

    /** @return array<string, string> */
    public function __invoke(ChangePasswordCommand $command): array
    {
        if (!$this->passwordHasher->isPasswordValid($command->user, $command->currentPassword)) {
            throw new UnauthorizedHttpException('Bearer', 'Mot de passe actuel incorrect.');
        }

        $errors = PasswordPolicy::validate($command->newPassword);
        if ([] !== $errors) {
            throw new BadRequestHttpException(implode(' ', $errors));
        }

        $command->user->setPassword($this->passwordHasher->hashPassword($command->user, $command->newPassword));
        $this->userRepository->save($command->user);
        $this->securityLogService->log(SecurityEventType::PASSWORD_CHANGED, $command->user);

        return ['message' => 'Mot de passe modifié avec succès.'];
    }
}
