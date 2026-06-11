<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
final class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function findValidByHash(string $tokenHash): ?PasswordResetToken
    {
        $token = $this->findOneBy(['tokenHash' => $tokenHash]);
        if (!$token instanceof PasswordResetToken || $token->isUsed() || $token->isExpired()) {
            return null;
        }

        return $token;
    }

    public function invalidateForUser(User $user): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.user = :user')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function save(PasswordResetToken $token, bool $flush = true): void
    {
        $this->getEntityManager()->persist($token);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
