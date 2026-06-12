<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Appointment\Enum\AppointmentSlotStatus;
use App\Entity\Candidate;
use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CounselorAvailabilitySlot>
 */
class CounselorAvailabilitySlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CounselorAvailabilitySlot::class);
    }

    public function save(CounselorAvailabilitySlot $slot, bool $flush = true): void
    {
        $this->getEntityManager()->persist($slot);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return list<CounselorAvailabilitySlot>
     */
    public function findAvailableForCounselor(
        User $counselor,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.counselor) = :counselorId')
            ->andWhere('s.status = :status')
            ->andWhere('s.startsAt >= :from')
            ->andWhere('s.startsAt < :to')
            ->setParameter('counselorId', $counselor->getId(), 'uuid')
            ->setParameter('status', AppointmentSlotStatus::AVAILABLE->value)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<CounselorAvailabilitySlot>
     */
    public function findBookedForCandidate(Candidate $candidate): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.candidate) = :candidateId')
            ->andWhere('s.status = :status')
            ->setParameter('candidateId', $candidate->getId(), 'uuid')
            ->setParameter('status', AppointmentSlotStatus::BOOKED->value)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<CounselorAvailabilitySlot>
     */
    public function findForCounselorBetween(
        User $counselor,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.counselor) = :counselorId')
            ->andWhere('s.startsAt >= :from')
            ->andWhere('s.startsAt < :to')
            ->setParameter('counselorId', $counselor->getId(), 'uuid')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countForCounselorFrom(User $counselor, \DateTimeImmutable $from): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('IDENTITY(s.counselor) = :counselorId')
            ->andWhere('s.startsAt >= :from')
            ->setParameter('counselorId', $counselor->getId(), 'uuid')
            ->setParameter('from', $from)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByCounselorAndStart(
        User $counselor,
        \DateTimeImmutable $startsAt,
    ): ?CounselorAvailabilitySlot {
        return $this->createQueryBuilder('s')
            ->andWhere('IDENTITY(s.counselor) = :counselorId')
            ->andWhere('s.startsAt = :startsAt')
            ->setParameter('counselorId', $counselor->getId(), 'uuid')
            ->setParameter('startsAt', $startsAt)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
