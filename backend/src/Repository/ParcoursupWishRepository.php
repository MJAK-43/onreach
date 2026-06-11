<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParcoursupWish;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParcoursupWish>
 */
final class ParcoursupWishRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParcoursupWish::class);
    }
}
