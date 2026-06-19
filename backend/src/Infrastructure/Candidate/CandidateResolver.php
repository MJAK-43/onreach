<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Entity\Candidate;
use App\Entity\User;
use App\Repository\CandidateRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class CandidateResolver
{
    public function __construct(
        private CandidateRepository $candidateRepository,
    ) {
    }

    public function resolveForUser(User $user): Candidate
    {
        if (!$user->hasRole('CANDIDATE')) {
            throw new AccessDeniedException('Réservé aux candidats.');
        }

        $candidate = $this->candidateRepository->findOneByEmailWithCounselorAndDocuments($user->getEmail());
        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Aucun dossier candidat associé à ce compte.');
        }

        return $candidate;
    }
}
