<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\Candidate;
use App\Entity\User;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Repository\CandidateRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCandidateCounselorController extends AbstractController
{
    public function __construct(
        private readonly CandidateRepository $candidateRepository,
        private readonly UserRepository $userRepository,
        private readonly CandidateTimelineService $timelineService,
    ) {
    }

    #[Route('/candidates/{id}/counselor', name: 'admin_candidates_assign_counselor', methods: ['PATCH'])]
    public function assignCounselor(string $id, Request $request): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            throw new NotFoundHttpException('Candidat introuvable.');
        }

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !\array_key_exists('counselorId', $payload)) {
            throw new BadRequestHttpException('Le champ counselorId est requis (null pour retirer).');
        }

        $candidate = $this->candidateRepository->find(Uuid::fromString($id));
        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Candidat introuvable.');
        }

        $counselorId = $payload['counselorId'];
        $counselor = null;
        if (null !== $counselorId && '' !== $counselorId) {
            if (!\is_string($counselorId) || !Uuid::isValid($counselorId)) {
                throw new BadRequestHttpException('Identifiant conseiller invalide.');
            }

            $counselor = $this->userRepository->find(Uuid::fromString($counselorId));
            if (!$counselor instanceof User || !$counselor->hasRole('COUNSELOR')) {
                throw new BadRequestHttpException('Conseiller introuvable.');
            }
        }

        $previous = $candidate->getAssignedCounselor();
        $candidate->setAssignedCounselor($counselor);
        $this->candidateRepository->save($candidate, true);

        if (
            (!$previous instanceof User && $counselor instanceof User)
            || ($previous instanceof User && (!$counselor instanceof User || !$previous->getId()->equals($counselor->getId())))
        ) {
            $label = $counselor instanceof User
                ? sprintf('%s %s', $counselor->getFirstName(), $counselor->getLastName())
                : 'aucun';
            $this->timelineService->record(
                $candidate,
                'candidate.counselor_assigned',
                sprintf('Conseiller attribué : %s', $label),
            );
        }

        return new JsonResponse($this->serializeCandidate($candidate));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCandidate(Candidate $candidate): array
    {
        $counselor = $candidate->getAssignedCounselor();

        return [
            'id' => $candidate->getId()->toRfc4122(),
            'firstName' => $candidate->getFirstName(),
            'lastName' => $candidate->getLastName(),
            'referenceNumber' => $candidate->getReferenceNumber(),
            'assignedCounselor' => $counselor instanceof User ? [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
            ] : null,
        ];
    }
}
