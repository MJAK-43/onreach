<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MfaRecoveryCode;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MfaRecoveryCode>
 */
final class MfaRecoveryCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MfaRecoveryCode::class);
    }

    /**
     * @return list<MfaRecoveryCode>
     */
    public function findUnusedForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.usedAt IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function deleteForUser(User $user): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function save(MfaRecoveryCode $code, bool $flush = true): void
    {
        $this->getEntityManager()->persist($code);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
