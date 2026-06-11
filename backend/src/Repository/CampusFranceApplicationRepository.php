<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CampusFranceApplication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CampusFranceApplication>
 */
final class CampusFranceApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampusFranceApplication::class);
    }
}
