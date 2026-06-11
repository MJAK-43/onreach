<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FinancingProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FinancingProfile>
 */
final class FinancingProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancingProfile::class);
    }
}
