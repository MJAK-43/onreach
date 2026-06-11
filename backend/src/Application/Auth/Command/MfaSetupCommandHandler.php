<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Entity\User;
use App\Infrastructure\Security\MfaService;
use App\Repository\UserRepository;

final readonly class MfaSetupCommandHandler
{
    public function __construct(
        private MfaService $mfaService,
        private UserRepository $userRepository,
    ) {
    }

    /**
     * @return array{secret: string, qrCode: string}
     */
    public function __invoke(User $user): array
    {
        $secret = $this->mfaService->generateSecret();
        $user->setMfaSecret($secret);
        $user->setMfaEnabled(false);
        $this->userRepository->save($user);

        return [
            'secret' => $secret,
            'qrCode' => $this->mfaService->getQrCodeDataUri($user, $secret),
        ];
    }
}
