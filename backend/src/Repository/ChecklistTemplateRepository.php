<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChecklistTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChecklistTemplate>
 */
final class ChecklistTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChecklistTemplate::class);
    }
}
