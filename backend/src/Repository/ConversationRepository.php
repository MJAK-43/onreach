<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Conversation> */
final class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findOneByPair(Candidate $candidate, User $counselor): ?Conversation
    {
        return $this->findOneBy([
            'candidate' => $candidate,
            'counselor' => $counselor,
        ]);
    }

    /** @return list<Conversation> */
    public function findForUser(User $user): array
    {
        if ($user->hasRole('CANDIDATE')) {
            return $this->createQueryBuilder('c')
                ->innerJoin('c.candidate', 'candidate')->addSelect('candidate')
                ->innerJoin('c.counselor', 'counselor')->addSelect('counselor')
                ->andWhere('LOWER(candidate.email) = :email')
                ->setParameter('email', mb_strtolower($user->getEmail()))
                ->orderBy('c.updatedAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        if ($user->hasRole('COUNSELOR') || $user->hasRole('ADMIN') || $user->hasRole('SUPER_ADMIN')) {
            $qb = $this->createQueryBuilder('c')
                ->innerJoin('c.candidate', 'candidate')->addSelect('candidate')
                ->innerJoin('c.counselor', 'counselor')->addSelect('counselor')
                ->orderBy('c.updatedAt', 'DESC');

            if ($user->hasRole('COUNSELOR') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER_ADMIN')) {
                $qb->andWhere('c.counselor = :counselor')->setParameter('counselor', $user);
            }

            /** @var list<Conversation> $results */
            $results = $qb->getQuery()->getResult();

            return $results;
        }

        return [];
    }
}
