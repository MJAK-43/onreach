<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidateDashboardService;
use App\Infrastructure\Candidate\CandidateResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
final class MeDashboardController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly CandidateDashboardService $dashboardService,
    ) {
    }

    #[Route('/dashboard', name: 'me_dashboard', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function dashboard(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $candidate = $this->candidateResolver->resolveForUser($user);

        return new JsonResponse($this->dashboardService->build($candidate));
    }
}
