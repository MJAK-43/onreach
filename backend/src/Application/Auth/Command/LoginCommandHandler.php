<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\SecurityEventType;
use App\Entity\User;
use App\Infrastructure\Security\LoginRateLimiter;
use App\Infrastructure\Security\MfaService;
use App\Infrastructure\Security\SecurityLogService;
use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class LoginCommandHandler
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private SecurityLogService $securityLogService,
        private LoginRateLimiter $rateLimiter,
        private MfaService $mfaService,
        private int $refreshTokenTtl = 2592000,
        private int $rememberMeTtl = 7776000,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(LoginCommand $command): array
    {
        if (!$this->rateLimiter->consume($command->email, $command->ip)) {
            throw new TooManyRequestsHttpException(60, 'Trop de tentatives. Réessayez plus tard.');
        }

        $user = $this->userRepository->findByEmail($command->email);

        if (!$user instanceof User || !$user->isActive()) {
            $this->securityLogService->log(SecurityEventType::LOGIN_FAILED, email: $command->email);
            throw new UnauthorizedHttpException('Bearer', 'Identifiants invalides.');
        }

        if ($user->isLocked()) {
            throw new AccessDeniedHttpException('Compte temporairement verrouillé.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $command->password)) {
            $user->incrementFailedLoginAttempts();
            if ($user->getFailedLoginAttempts() >= self::MAX_ATTEMPTS) {
                $user->setLockedUntil(new \DateTimeImmutable('+'.self::LOCK_MINUTES.' minutes'));
            }
            $this->userRepository->save($user);
            $this->securityLogService->log(SecurityEventType::LOGIN_FAILED, $user, $command->email);

            throw new UnauthorizedHttpException('Bearer', 'Identifiants invalides.');
        }

        if ($user->isMfaEnabled()) {
            if (null === $command->mfaCode) {
                return ['requiresMfa' => true];
            }
            $validMfa = $this->mfaService->verifyCode($user, $command->mfaCode)
                || $this->mfaService->verifyRecoveryCode($user, $command->mfaCode);
            if (!$validMfa) {
                $this->securityLogService->log(SecurityEventType::LOGIN_FAILED, $user, metadata: ['reason' => 'invalid_mfa']);

                throw new UnauthorizedHttpException('Bearer', 'Code MFA invalide.');
            }
        }

        $user->resetFailedLoginAttempts();
        $this->userRepository->save($user);
        $this->securityLogService->log(SecurityEventType::LOGIN_SUCCESS, $user);

        $accessToken = $this->jwtManager->create($user);
        $ttl = $command->rememberMe ? $this->rememberMeTtl : $this->refreshTokenTtl;
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, $ttl);
        $this->refreshTokenManager->save($refreshToken);

        return [
            'token' => $accessToken,
            'refreshToken' => $refreshToken->getRefreshToken(),
            'expiresIn' => 3600,
            'user' => $this->serializeUser($user),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'roles' => array_map(fn ($r) => str_replace('ROLE_', '', $r), $user->getRoles()),
            'permissions' => $user->getPermissions(),
            'mfaEnabled' => $user->isMfaEnabled(),
        ];
    }
}
