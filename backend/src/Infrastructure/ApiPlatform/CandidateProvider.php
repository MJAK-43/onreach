<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Candidate;
use App\Entity\User;
use App\Repository\CandidateRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Candidate>
 */
final readonly class CandidateProvider implements ProviderInterface
{
    public function __construct(
        private CandidateRepository $candidateRepository,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->provideCollection();
        }

        $candidate = $this->candidateRepository->find($uriVariables['id'] ?? null);
        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Candidat introuvable.');
        }

        $this->assertCanAccess($candidate);

        return $candidate;
    }

    /**
     * @return list<Candidate>
     */
    private function provideCollection(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN')) {
            return $this->candidateRepository->findAllForList();
        }

        if ($user->hasRole('COUNSELOR')) {
            return $this->candidateRepository->findForCounselor($user);
        }

        if ($user->hasRole('CANDIDATE')) {
            $own = $this->candidateRepository->findOneByEmailWithCounselorAndDocuments($user->getEmail());

            return $own instanceof Candidate ? [$own] : [];
        }

        throw new AccessDeniedHttpException();
    }

    private function assertCanAccess(Candidate $candidate): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN')) {
            return;
        }

        if ($user->hasRole('COUNSELOR') && $candidate->getAssignedCounselor() === $user) {
            return;
        }

        if ($user->hasRole('CANDIDATE') && strtolower($candidate->getEmail()) === strtolower($user->getEmail())) {
            return;
        }

        throw new AccessDeniedHttpException();
    }
}
