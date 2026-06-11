<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\SecurityEventType;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Infrastructure\Mail\PasswordResetMailer;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ForgotPasswordCommandHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $tokenRepository,
        private SecurityLogService $securityLogService,
        private PasswordResetMailer $passwordResetMailer,
        #[Autowire('%kernel.environment%')]
        private string $environment,
    ) {
    }

    /**
     * Always returns success message to prevent user enumeration.
     *
     * @return array{message: string, resetToken?: string}
     */
    public function __invoke(ForgotPasswordCommand $command): array
    {
        $user = $this->userRepository->findByEmail($command->email);
        $response = ['message' => 'Si un compte existe, un email de réinitialisation a été envoyé.'];

        if (!$user instanceof User || !$user->isActive()) {
            return $response;
        }

        $plainToken = bin2hex(random_bytes(32));
        $this->tokenRepository->invalidateForUser($user);
        $token = new PasswordResetToken(
            $user,
            hash('sha256', $plainToken),
            new \DateTimeImmutable('+1 hour'),
        );
        $this->tokenRepository->save($token);
        $this->securityLogService->log(SecurityEventType::PASSWORD_RESET_REQUESTED, $user);
        $this->passwordResetMailer->send($user, $plainToken);

        if ('test' === $this->environment) {
            $response['resetToken'] = $plainToken;
        }

        return $response;
    }
}
