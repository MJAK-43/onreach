<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\PathwayAuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PathwayAuditLog> */
final class PathwayAuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PathwayAuditLog::class);
    }

    /**
     * @return list<PathwayAuditLog>
     */
    public function findRecentByCandidate(Candidate $candidate, int $limit = 50): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.candidate = :candidate')
            ->setParameter('candidate', $candidate)
            ->orderBy('l.occurredAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
