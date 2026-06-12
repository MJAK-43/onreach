<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use ApiPlatform\Metadata\Get;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\CandidateProvider;
use App\Infrastructure\Candidate\CandidateTimelineService;
use App\Infrastructure\Candidate\DocumentStorageService;
use App\Repository\CandidateDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/candidates')]
final class CandidateDocumentController extends AbstractController
{
    public function __construct(
        private readonly CandidateProvider $candidateProvider,
        private readonly DocumentStorageService $storageService,
        private readonly CandidateDocumentRepository $documentRepository,
        private readonly CandidateTimelineService $timelineService,
    ) {
    }

    #[Route('/{id}/documents/upload', name: 'candidate_document_upload', methods: ['POST'])]
    #[IsGranted('documents.upload')]
    public function upload(string $id, Request $request): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $file = $request->files->get('file');
        $typeValue = $request->request->getString('type');
        if (!$file || '' === $typeValue) {
            throw new BadRequestHttpException('Fichier et type requis.');
        }

        $type = DocumentType::tryFrom($typeValue);
        if (null === $type) {
            throw new BadRequestHttpException('Type de document invalide.');
        }

        $document = new CandidateDocument($candidate, $type, DocumentStatus::UPLOADED);
        $storagePath = $this->storageService->store($file, $candidate->getId()->toRfc4122(), $document->getId()->toRfc4122());
        $document->markUploaded(
            $file->getClientOriginalName() ?? 'file',
            $file->getClientMimeType() ?? 'application/octet-stream',
            (int) $file->getSize(),
            $storagePath,
        );
        $this->documentRepository->save($document);
        $this->timelineService->record($candidate, 'document.uploaded', 'Document téléversé : '.$type->label());

        return new JsonResponse([
            'id' => $document->getId()->toRfc4122(),
            'type' => $document->getType()->value,
            'status' => $document->getStatus()->value,
            'originalFilename' => $document->getOriginalFilename(),
        ], 201);
    }

    #[Route('/{id}/documents/{documentId}/validate', name: 'candidate_document_validate', methods: ['POST'])]
    #[IsGranted('documents.validate')]
    public function validate(string $id, string $documentId): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $document = $this->documentRepository->find($documentId);
        if (!$document instanceof CandidateDocument || $document->getCandidate()->getId() !== $candidate->getId()) {
            throw $this->createNotFoundException();
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $document->markValidated($user);
        $this->documentRepository->save($document);
        $this->timelineService->record($candidate, 'document.validated', 'Document validé : '.$document->getType()->label());

        return new JsonResponse(['status' => $document->getStatus()->value]);
    }

    #[Route('/{id}/documents/{documentId}/reject', name: 'candidate_document_reject', methods: ['POST'])]
    #[IsGranted('documents.validate')]
    public function reject(string $id, string $documentId, Request $request): JsonResponse
    {
        $candidate = $this->loadCandidate($id);
        $document = $this->documentRepository->find($documentId);
        if (!$document instanceof CandidateDocument || $document->getCandidate()->getId() !== $candidate->getId()) {
            throw $this->createNotFoundException();
        }

        $payload = json_decode($request->getContent(), true);
        $reason = trim((string) (is_array($payload) ? ($payload['reason'] ?? '') : ''));
        if ('' === $reason) {
            throw new BadRequestHttpException('Motif de refus obligatoire.');
        }

        $document->markRejected($reason);
        $this->documentRepository->save($document);
        $user = $this->getUser();
        $this->timelineService->record(
            $candidate,
            'document.rejected',
            'Document refusé : '.$document->getType()->label(),
            ['reason' => $reason],
            $user instanceof User ? $user : null,
        );

        return new JsonResponse(['status' => $document->getStatus()->value, 'rejectionReason' => $reason]);
    }

    private function loadCandidate(string $id): Candidate
    {
        $result = $this->candidateProvider->provide(new Get(), ['id' => $id]);
        if (!$result instanceof Candidate) {
            throw $this->createNotFoundException();
        }

        return $result;
    }
}
