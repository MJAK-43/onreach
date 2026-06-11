<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LanguageProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LanguageProfile>
 */
final class LanguageProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LanguageProfile::class);
    }
}
