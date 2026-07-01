<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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

    /**
     * @return list<CandidatePathway>
     */
    public function findForTracking(
        ?PathwayCode $pathwayCode,
        ?PathwayInstanceStatus $status,
        ?int $campaignYear,
        ?Uuid $counselorId,
        ?string $search,
        ?User $scopeCounselor,
    ): array {
        $qb = $this->createQueryBuilder('cp')
            ->innerJoin('cp.candidate', 'c')->addSelect('c')
            ->innerJoin('cp.pathwayTemplate', 'pt')->addSelect('pt')
            ->innerJoin('pt.campaign', 'camp')->addSelect('camp')
            ->leftJoin('c.assignedCounselor', 'counselor')->addSelect('counselor')
            ->leftJoin('cp.stages', 'stage')->addSelect('stage')
            ->leftJoin('stage.subSteps', 'ss')->addSelect('ss')
            ->leftJoin('ss.subStepTemplate', 'sst')->addSelect('sst')
            ->orderBy('cp.updatedAt', 'DESC');

        if ($scopeCounselor instanceof User) {
            $qb->andWhere('counselor.id = :scopeCounselorId')
                ->setParameter('scopeCounselorId', $scopeCounselor->getId(), 'uuid');
        }

        if ($pathwayCode instanceof PathwayCode) {
            $qb->andWhere('pt.code = :pathwayCode')
                ->setParameter('pathwayCode', $pathwayCode);
        }

        if ($status instanceof PathwayInstanceStatus) {
            $qb->andWhere('cp.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $campaignYear) {
            $qb->andWhere('camp.year = :campaignYear')
                ->setParameter('campaignYear', $campaignYear);
        }

        if ($counselorId instanceof Uuid) {
            $qb->andWhere('counselor.id = :counselorId')
                ->setParameter('counselorId', $counselorId, 'uuid');
        }

        if (null !== $search && '' !== $search) {
            $qb->andWhere(
                'LOWER(c.firstName) LIKE :search OR LOWER(c.lastName) LIKE :search OR LOWER(c.email) LIKE :search',
            )->setParameter('search', '%'.mb_strtolower($search).'%');
        }

        /** @var list<CandidatePathway> $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findOneForCandidate(Candidate $candidate, Uuid $pathwayId): ?CandidatePathway
    {
        return $this->createQueryBuilder('cp')
            ->where('IDENTITY(cp.candidate) = :candidateId')
            ->andWhere('cp.id = :pathwayId')
            ->setParameter('candidateId', $candidate->getId(), 'uuid')
            ->setParameter('pathwayId', $pathwayId, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneForProgressRefresh(Uuid $pathwayId): ?CandidatePathway
    {
        return $this->createQueryBuilder('cp')
            ->leftJoin('cp.stages', 'st')->addSelect('st')
            ->leftJoin('st.subSteps', 'ss')->addSelect('ss')
            ->leftJoin('ss.subStepTemplate', 'sst')->addSelect('sst')
            ->leftJoin('cp.pathwayTemplate', 'pt')->addSelect('pt')
            ->where('cp.id = :pathwayId')
            ->setParameter('pathwayId', $pathwayId, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
