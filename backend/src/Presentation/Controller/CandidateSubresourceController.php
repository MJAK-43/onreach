<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use ApiPlatform\Metadata\Get;
use App\Domain\Candidate\Enum\ApplicationType;
use App\Entity\Candidate;
use App\Entity\CandidateNote;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\CandidateProvider;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Infrastructure\Candidate\ChecklistService;
use App\Repository\CandidateNoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/candidates')]
final class CandidateSubresourceController extends AbstractController
{
    public function __construct(
        private readonly CandidateProvider $candidateProvider,
        private readonly ChecklistService $checklistService,
        private readonly CandidateNoteRepository $noteRepository,
        private readonly CandidateTimelineService $timelineService,
    ) {
    }

    #[Route('/{id}/timeline', name: 'candidate_timeline', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function timeline(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $entries = array_map(static fn ($entry) => [
            'id' => $entry->getId()->toRfc4122(),
            'action' => $entry->getAction(),
            'description' => $entry->getDescription(),
            'metadata' => $entry->getMetadata(),
            'occurredAt' => $entry->getOccurredAt()->format(\DateTimeInterface::ATOM),
            'actor' => $entry->getActor() ? trim($entry->getActor()->getFirstName().' '.$entry->getActor()->getLastName()) : null,
        ], $candidate->getTimelineEntries()->toArray());

        return new JsonResponse($entries);
    }

    #[Route('/{id}/documents', name: 'candidate_documents', methods: ['GET'])]
    #[IsGranted('documents.view')]
    public function documents(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $documents = array_map(static fn ($doc) => [
            'id' => $doc->getId()->toRfc4122(),
            'type' => $doc->getType()->value,
            'status' => $doc->getStatus()->value,
            'originalFilename' => $doc->getOriginalFilename(),
            'mimeType' => $doc->getMimeType(),
            'size' => $doc->getSize(),
            'uploadedAt' => $doc->getUploadedAt()?->format(\DateTimeInterface::ATOM),
            'validatedAt' => $doc->getValidatedAt()?->format(\DateTimeInterface::ATOM),
        ], $candidate->getDocuments()->toArray());

        return new JsonResponse($documents);
    }

    #[Route('/{id}/applications', name: 'candidate_applications', methods: ['GET'])]
    #[IsGranted('applications.view')]
    public function applications(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);

        $cf = $candidate->getCampusFranceApplication();
        $ps = $candidate->getParcoursupApplication();
        $sl = $candidate->getParisSaclayApplication();

        return new JsonResponse([
            'campusFrance' => $cf ? [
                'status' => $cf->getStatus()->value,
                'studyProject' => $cf->getStudyProject(),
                'professionalProject' => $cf->getProfessionalProject(),
            ] : null,
            'parcoursup' => $ps ? [
                'ineNumber' => $ps->getIneNumber(),
                'highSchool' => $ps->getHighSchool(),
                'motivationProject' => $ps->getMotivationProject(),
            ] : null,
            'parisSaclay' => $sl ? [
                'degreeLevel' => $sl->getDegreeLevel()->value,
                'researchProject' => $sl->getResearchProject(),
            ] : null,
        ]);
    }

    #[Route('/{id}/notes', name: 'candidate_notes', methods: ['GET'])]
    #[IsGranted('candidates.notes')]
    public function notes(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $notes = array_map(static fn (CandidateNote $note) => [
            'id' => $note->getId()->toRfc4122(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'author' => trim($note->getAuthor()->getFirstName().' '.$note->getAuthor()->getLastName()),
            'createdAt' => $note->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $candidate->getNotes()->toArray());

        return new JsonResponse($notes);
    }

    #[Route('/{id}/notes', name: 'candidate_note_create', methods: ['POST'])]
    #[IsGranted('candidates.notes')]
    public function createNote(string $id, Request $request): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $content = trim((string) ($payload['content'] ?? ''));
        if ('' === $title || '' === $content) {
            throw new BadRequestHttpException('Titre et contenu requis.');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $note = new CandidateNote($candidate, $user, $title, $content);
        $this->noteRepository->save($note);
        $this->timelineService->record($candidate, 'note.added', 'Note ajoutée : '.$title);

        return new JsonResponse([
            'id' => $note->getId()->toRfc4122(),
            'title' => $note->getTitle(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}/completion', name: 'candidate_completion', methods: ['GET'])]
    #[IsGranted('candidates.view')]
    public function completion(string $id): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $campus = $this->checklistService->getProgressForCandidate($candidate, ApplicationType::CAMPUS_FRANCE);

        return new JsonResponse([
            'global' => $candidate->getCompletionPercent(),
            'profile' => $candidate->getCompletionPercent(),
            'documents' => $campus['percent'],
            'financing' => null !== $candidate->getFinancingProfile() ? 100 : 0,
            'campusFrance' => $campus['percent'],
            'checklist' => $campus,
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
