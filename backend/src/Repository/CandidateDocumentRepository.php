<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CandidateDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CandidateDocument>
 */
final class CandidateDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateDocument::class);
    }

    public function save(CandidateDocument $document, bool $flush = true): void
    {
        $this->getEntityManager()->persist($document);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CandidateDocument $document, bool $flush = true): void
    {
        $this->getEntityManager()->remove($document);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
