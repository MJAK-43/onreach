<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Candidate\Enum\CandidateStatus;
use App\Entity\Candidate;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function save(Candidate $candidate, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidate);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Candidate $candidate, bool $flush = true): void
    {
        $this->getEntityManager()->remove($candidate);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countForYear(int $year): int
    {
        $start = new \DateTimeImmutable("{$year}-01-01 00:00:00");
        $end = new \DateTimeImmutable("{$year}-12-31 23:59:59");

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<Candidate>
     */
    public function findForCounselor(User $counselor, ?CandidateStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.assignedCounselor = :counselor')
            ->setParameter('counselor', $counselor)
            ->orderBy('c.updatedAt', 'DESC');

        if (null !== $status) {
            $qb->andWhere('c.status = :status')->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }
}
