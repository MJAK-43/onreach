<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PathwayStageTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PathwayStageTemplate> */
final class PathwayStageTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PathwayStageTemplate::class);
    }
}
