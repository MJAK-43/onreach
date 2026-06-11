<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParcoursupApplication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParcoursupApplication>
 */
final class ParcoursupApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParcoursupApplication::class);
    }
}
