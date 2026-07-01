<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Security\PasswordPolicy;
use App\Domain\Security\SecurityEventType;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateOnboardingService;
use App\Infrastructure\Candidate\CandidateRegistrationData;
use App\Infrastructure\Security\LoginRateLimiter;
use App\Infrastructure\Security\SecurityLogService;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final readonly class RegisterCommandHandler
{
    public function __construct(
        private CandidateOnboardingService $onboardingService,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private SecurityLogService $securityLogService,
        private LoginRateLimiter $rateLimiter,
        private int $refreshTokenTtl = 2592000,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(RegisterCommand $command): array
    {
        if (!$this->rateLimiter->consume($command->email, $command->ip)) {
            throw new TooManyRequestsHttpException(60, 'Trop de tentatives. Réessayez plus tard.');
        }

        $passwordErrors = PasswordPolicy::validate($command->password);
        if ([] !== $passwordErrors) {
            throw new BadRequestHttpException(implode(' ', $passwordErrors));
        }

        $user = $this->onboardingService->register(new CandidateRegistrationData(
            firstName: $command->firstName,
            lastName: $command->lastName,
            email: $command->email,
            password: $command->password,
            nationality: $command->nationality,
            studyApplicationType: $command->studyApplicationType,
            phone: $command->phone,
            city: $command->city,
            country: $command->country,
        ));

        $this->securityLogService->log(SecurityEventType::REGISTRATION, $user);

        $accessToken = $this->jwtManager->create($user);
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, $this->refreshTokenTtl);
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
