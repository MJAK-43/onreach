<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AcademicRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AcademicRecord>
 */
final class AcademicRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcademicRecord::class);
    }
}
