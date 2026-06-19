<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CandidatePathway> */
final class CandidatePathwayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidatePathway::class);
    }

    /** @return list<CandidatePathway> */
    public function findByCandidate(Candidate $candidate): array
    {
        return $this->findBy(['candidate' => $candidate], ['createdAt' => 'ASC']);
    }
}
