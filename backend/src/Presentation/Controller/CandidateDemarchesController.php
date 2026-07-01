<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use ApiPlatform\Metadata\Get;
use App\Entity\Candidate;
use App\Infrastructure\ApiPlatform\CandidateProvider;
use App\Infrastructure\Candidate\CandidatePortalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/candidates')]
final class CandidateDemarchesController extends AbstractController
{
    public function __construct(
        private readonly CandidateProvider $candidateProvider,
        private readonly CandidatePortalService $portalService,
    ) {
    }

    #[Route('/{id}/demarches/overview', name: 'candidate_demarches_overview', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function overview(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        return new JsonResponse($this->portalService->getApplicationsOverview($candidate));
    }

    #[Route('/{id}/demarches/campus-france', name: 'candidate_demarches_campus_france', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function campusFrance(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        return new JsonResponse($this->portalService->getCampusFrance($candidate));
    }

    #[Route('/{id}/demarches/parcoursup', name: 'candidate_demarches_parcoursup', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function parcoursup(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        return new JsonResponse($this->portalService->getParcoursup($candidate));
    }

    #[Route('/{id}/demarches/paris-saclay', name: 'candidate_demarches_paris_saclay', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function parisSaclay(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        return new JsonResponse($this->portalService->getParisSaclay($candidate));
    }

    private function loadCandidate(string $id): Candidate
    {
        $operation = new Get();
        $result = $this->candidateProvider->provide($operation, ['id' => $id]);
        if (!$result instanceof Candidate) {
            throw $this->createNotFoundException();
        }

        return $result;
    }
}
