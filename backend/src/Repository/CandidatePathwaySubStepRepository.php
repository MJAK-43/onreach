<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CandidatePathwaySubStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/** @extends ServiceEntityRepository<CandidatePathwaySubStep> */
final class CandidatePathwaySubStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidatePathwaySubStep::class);
    }

    public function findOneForCandidatePathway(Uuid $candidatePathwayId, Uuid $subStepId): ?CandidatePathwaySubStep
    {
        return $this->createQueryBuilder('ss')
            ->innerJoin('ss.candidateStage', 's')->addSelect('s')
            ->innerJoin('s.candidatePathway', 'cp')->addSelect('cp')
            ->innerJoin('ss.subStepTemplate', 'sst')->addSelect('sst')
            ->where('cp.id = :pathwayId')
            ->andWhere('ss.id = :subStepId')
            ->setParameter('pathwayId', $candidatePathwayId, 'uuid')
            ->setParameter('subStepId', $subStepId, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<CandidatePathwaySubStep>
     */
    public function findDueForReminder(\DateTimeImmutable $until): array
    {
        $today = new \DateTimeImmutable('today');

        return $this->createQueryBuilder('ss')
            ->innerJoin('ss.candidateStage', 's')
            ->innerJoin('s.candidatePathway', 'cp')
            ->innerJoin('cp.candidate', 'c')
            ->innerJoin('ss.subStepTemplate', 'sst')->addSelect('sst')
            ->innerJoin('cp.pathwayTemplate', 'pt')->addSelect('pt')
            ->leftJoin('c.assignedCounselor', 'counselor')->addSelect('counselor')
            ->andWhere('ss.dueDate IS NOT NULL')
            ->andWhere('ss.dueDate <= :until')
            ->andWhere('ss.dueReminderSentAt IS NULL')
            ->andWhere('ss.counselorValidatedAt IS NULL')
            ->andWhere('ss.adminValidatedAt IS NULL')
            ->setParameter('until', $until)
            ->orderBy('ss.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
