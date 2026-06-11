<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Entity\MfaRecoveryCode;
use App\Entity\User;
use App\Repository\MfaRecoveryCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final readonly class MfaService
{
    private const ISSUER = "On'Reach";

    public function __construct(
        private MfaRecoveryCodeRepository $recoveryCodeRepository,
        private EntityManagerInterface $entityManager,
        private PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    public function generateSecret(): string
    {
        return TOTP::generate()->getSecret();
    }

    public function getProvisioningUri(User $user, string $secret): string
    {
        $totp = TOTP::create($secret);
        $totp->setLabel($user->getEmail());
        $totp->setIssuer(self::ISSUER);

        return $totp->getProvisioningUri();
    }

    public function getQrCodeDataUri(User $user, string $secret): string
    {
        $uri = $this->getProvisioningUri($user, $secret);
        $qrCode = new QrCode($uri);
        $writer = new PngWriter();

        return $writer->write($qrCode)->getDataUri();
    }

    public function verifyCode(User $user, string $code): bool
    {
        $secret = $user->getMfaSecret();
        if (null === $secret) {
            return false;
        }

        return TOTP::create($secret)->verify($code);
    }

    /**
     * @return list<string> Plain recovery codes (shown once)
     */
    public function generateRecoveryCodes(User $user, int $count = 8): array
    {
        $this->recoveryCodeRepository->deleteForUser($user);
        $hasher = $this->hasherFactory->getPasswordHasher(MfaRecoveryCode::class);
        $plainCodes = [];

        for ($i = 0; $i < $count; ++$i) {
            $plain = bin2hex(random_bytes(4)).'-'.bin2hex(random_bytes(4));
            $plainCodes[] = $plain;
            $entity = new MfaRecoveryCode($user, $hasher->hash($plain));
            $this->recoveryCodeRepository->save($entity, false);
        }

        $this->entityManager->flush();

        return $plainCodes;
    }

    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $hasher = $this->hasherFactory->getPasswordHasher(MfaRecoveryCode::class);
        foreach ($this->recoveryCodeRepository->findUnusedForUser($user) as $recoveryCode) {
            if ($hasher->verify($recoveryCode->getCodeHash(), $code)) {
                $recoveryCode->markUsed();
                $this->recoveryCodeRepository->save($recoveryCode);

                return true;
            }
        }

        return false;
    }
}
