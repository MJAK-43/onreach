<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CandidateTimelineEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CandidateTimelineEntry>
 */
final class CandidateTimelineEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateTimelineEntry::class);
    }

    public function save(CandidateTimelineEntry $entry, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entry);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
