<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Candidate\CandidateCompletionService;
use App\Infrastructure\Candidate\CandidatePortalService;
use App\Infrastructure\Candidate\CandidateProfileService;
use App\Infrastructure\Candidate\CandidateResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
final class MeProfileController extends AbstractController
{
    public function __construct(
        private readonly CandidateResolver $candidateResolver,
        private readonly CandidateProfileService $profileService,
        private readonly CandidateCompletionService $completionService,
        private readonly CandidatePortalService $portalService,
    ) {
    }

    #[Route('/profile', name: 'me_profile_get', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function getProfile(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->profileService->serialize($candidate));
    }

    #[Route('/profile', name: 'me_profile_put', methods: ['PUT'])]
    #[IsGranted('profile.edit')]
    public function updateProfile(Request $request): JsonResponse
    {
        $candidate = $this->resolveCandidate();
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $user = $this->getUser();
        $this->profileService->update($candidate, $payload, $user instanceof User ? $user : null);

        return new JsonResponse($this->profileService->serialize($candidate));
    }

    #[Route('/completion', name: 'me_completion', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function completion(): JsonResponse
    {
        $candidate = $this->resolveCandidate();

        return new JsonResponse($this->completionService->compute($candidate));
    }

    #[Route('/history', name: 'me_history', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function history(): JsonResponse
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
