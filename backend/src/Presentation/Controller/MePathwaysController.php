<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidateResolver;
use App\Infrastructure\Pathway\PathwayAssignmentService;
use App\Infrastructure\Pathway\PathwaySerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
final class MePathwaysController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly PathwayAssignmentService $assignmentService,
        private readonly PathwaySerializer $serializer,
    ) {
    }

    #[Route('/pathways', name: 'me_pathways', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function list(): JsonResponse
    {
        $candidate = $this->resolveCandidate();
        $pathways = $this->assignmentService->listForCandidate($candidate);

        return new JsonResponse([
            'studyApplicationType' => $candidate->getStudyApplicationType()?->value,
            'studyApplicationTypeLabel' => $candidate->getStudyApplicationType()?->label(),
            'pathways' => $this->serializer->serializeMany($pathways),
        ]);
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
