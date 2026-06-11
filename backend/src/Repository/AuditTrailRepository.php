<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuditTrail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditTrail>
 */
final class AuditTrailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditTrail::class);
    }

    public function save(AuditTrail $auditTrail, bool $flush = true): void
    {
        $this->getEntityManager()->persist($auditTrail);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
