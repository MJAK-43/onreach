<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidateResolver;
use App\Infrastructure\Pathway\PathwaySerializer;
use App\Repository\CandidatePathwayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
final class MePathwaysController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly CandidatePathwayRepository $candidatePathwayRepository,
        private readonly PathwaySerializer $serializer,
    ) {
    }

    #[Route('/pathways', name: 'me_pathways', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function list(): JsonResponse
    {
        $candidate = $this->resolveCandidate();
        $pathways = $this->candidatePathwayRepository->findByCandidate($candidate);

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
