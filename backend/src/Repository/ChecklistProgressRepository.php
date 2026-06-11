<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ChecklistProgress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChecklistProgress>
 */
final class ChecklistProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChecklistProgress::class);
    }

    public function save(ChecklistProgress $progress, bool $flush = true): void
    {
        $this->getEntityManager()->persist($progress);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<string, ChecklistProgress>
     */
    public function findIndexedForCandidate(\App\Entity\Candidate $candidate): array
    {
        $rows = $this->findBy(['candidate' => $candidate]);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->getChecklistItem()->getId()->toRfc4122()] = $row;
        }

        return $indexed;
    }
}
