<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SecurityLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecurityLog>
 */
final class SecurityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityLog::class);
    }

    public function save(SecurityLog $log, bool $flush = true): void
    {
        $this->getEntityManager()->persist($log);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
