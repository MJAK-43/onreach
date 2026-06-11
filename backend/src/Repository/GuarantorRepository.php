<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Guarantor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guarantor>
 */
final class GuarantorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guarantor::class);
    }
}
