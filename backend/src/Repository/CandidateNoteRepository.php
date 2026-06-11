<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CandidateNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CandidateNote>
 */
final class CandidateNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateNote::class);
    }

    public function save(CandidateNote $note, bool $flush = true): void
    {
        $this->getEntityManager()->persist($note);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
