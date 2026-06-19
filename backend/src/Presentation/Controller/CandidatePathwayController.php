<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use ApiPlatform\Metadata\Get;
use App\Entity\Candidate;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\CandidateProvider;
use App\Infrastructure\Pathway\PathwayAuditSerializer;
use App\Infrastructure\Pathway\PathwayAssignmentService;
use App\Infrastructure\Pathway\PathwaySerializer;
use App\Infrastructure\Pathway\PathwaySubStepService;
use App\Repository\CandidatePathwaySubStepRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[Route('/api/candidates')]
final class CandidatePathwayController extends AbstractController
{
    public function __construct(
        private readonly CandidateProvider $candidateProvider,
        private readonly PathwayAssignmentService $assignmentService,
        private readonly PathwaySerializer $serializer,
        private readonly PathwaySubStepService $subStepService,
        private readonly CandidatePathwaySubStepRepository $subStepRepository,
        private readonly PathwayAuditSerializer $auditSerializer,
    ) {
    }

    #[Route('/{id}/pathways', name: 'candidate_pathways', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function list(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $pathways = $this->assignmentService->listForCandidate($candidate);

        return new JsonResponse([
            'studyApplicationType' => $candidate->getStudyApplicationType()?->value,
            'studyApplicationTypeLabel' => $candidate->getStudyApplicationType()?->label(),
            'pathways' => $this->serializer->serializeMany($pathways),
        ]);
    }

    #[Route('/{id}/pathways/{pathwayId}/sub-steps/{subStepId}', name: 'candidate_pathway_substep_patch', methods: ['PATCH'])]
    #[IsGranted('applications.edit')]
    public function patchSubStep(string $id, string $pathwayId, string $subStepId, Request $request): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        if (!Uuid::isValid($pathwayId) || !Uuid::isValid($subStepId)) {
            throw new NotFoundHttpException('Sous-étape introuvable.');
        }

        $subStep = $this->subStepRepository->findOneForCandidatePathway(
            Uuid::fromString($pathwayId),
            Uuid::fromString($subStepId),
        );
        if (!$subStep) {
            throw new NotFoundHttpException('Sous-étape introuvable.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $updated = $this->subStepService->updateValidation($candidate, $subStep, $payload, $user);
        $pathway = $updated->getCandidateStage()->getCandidatePathway();

        return new JsonResponse([
            'pathway' => $this->serializer->serializePathway($pathway),
        ]);
    }

    #[Route('/{id}/pathway-audit', name: 'candidate_pathway_audit', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function audit(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        return new JsonResponse([
            'items' => $this->auditSerializer->serializeForCandidate($candidate),
        ]);
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
