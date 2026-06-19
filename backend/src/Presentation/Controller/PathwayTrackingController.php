<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Pathway\PathwayStatsService;
use App\Infrastructure\Pathway\PathwayTrackingQueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/pathways')]
final class PathwayTrackingController extends AbstractController
{
    public function __construct(
        private readonly PathwayTrackingQueryService $trackingQueryService,
        private readonly PathwayStatsService $statsService,
    ) {
    }

    #[Route('/candidates', name: 'pathway_tracking_candidates', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function listCandidates(Request $request): JsonResponse
    {
        $filters = [
            'pathway' => $request->query->get('pathway'),
            'status' => $request->query->get('status'),
            'campaign' => $request->query->get('campaign'),
            'counselor' => $request->query->get('counselor'),
            'search' => $request->query->get('search'),
        ];

        return new JsonResponse($this->trackingQueryService->list($filters));
    }

    #[Route('/stats', name: 'pathway_tracking_stats', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function stats(): JsonResponse
    {
        return new JsonResponse($this->statsService->aggregate());
    }
}
