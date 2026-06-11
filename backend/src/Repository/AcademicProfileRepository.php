<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AcademicProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AcademicProfile>
 */
final class AcademicProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcademicProfile::class);
    }
}
