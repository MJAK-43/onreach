<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidatePortalService;
use App\Infrastructure\Candidate\CandidateResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
final class MeApplicationsController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly CandidatePortalService $portalService,
    ) {
    }

    #[Route('/applications', name: 'me_applications', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function applications(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->portalService->getApplicationsOverview($candidate));
    }

    #[Route('/campus-france', name: 'me_campus_france', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function campusFrance(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->portalService->getCampusFrance($candidate));
    }

    #[Route('/parcoursup', name: 'me_parcoursup', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function parcoursup(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->portalService->getParcoursup($candidate));
    }

    #[Route('/paris-saclay', name: 'me_paris_saclay', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function parisSaclay(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->portalService->getParisSaclay($candidate));
    }

    #[Route('/timeline', name: 'me_timeline', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function timeline(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->portalService->getTimeline($candidate));
    }

    private function resolveCandidate(): \App\Entity\Candidate
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->candidateResolver->resolveForUser($user);
    }
}
