<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\InAppNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<InAppNotification> */
final class InAppNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InAppNotification::class);
    }

    /**
     * @return list<InAppNotification>
     */
    public function findRecentForUser(User $user, int $limit = 30): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('IDENTITY(n.recipient) = :userId')
            ->setParameter('userId', $user->getId(), 'uuid')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('IDENTITY(n.recipient) = :userId')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('userId', $user->getId(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAllAsReadForUser(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.readAt', ':now')
            ->andWhere('IDENTITY(n.recipient) = :userId')
            ->andWhere('n.readAt IS NULL')
            ->setParameter('userId', $user->getId(), 'uuid')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
