<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Repository\CandidateDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class CandidateDocumentManager
{
    private const MAX_SIZE = 10_485_760; // 10 Mo

    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function __construct(
        private DocumentStorageService $storageService,
        private CandidateDocumentRepository $documentRepository,
        private CandidateTimelineService $timelineService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeDocument(CandidateDocument $document): array
    {
        return [
            'id' => $document->getId()->toRfc4122(),
            'type' => $document->getType()->value,
            'typeLabel' => $document->getType()->label(),
            'status' => $document->getStatus()->value,
            'statusLabel' => $document->getStatus()->label(),
            'originalFilename' => $document->getOriginalFilename(),
            'mimeType' => $document->getMimeType(),
            'size' => $document->getSize(),
            'version' => $document->getVersion(),
            'uploadedAt' => $document->getUploadedAt()?->format(\DateTimeInterface::ATOM),
            'validatedAt' => $document->getValidatedAt()?->format(\DateTimeInterface::ATOM),
            'rejectionReason' => $document->getRejectionReason(),
            'previewable' => $this->isPreviewable($document->getMimeType()),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listDocuments(Candidate $candidate): array
    {
        return array_map(
            fn (CandidateDocument $doc) => $this->serializeDocument($doc),
            $candidate->getDocuments()->toArray(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function upload(Candidate $candidate, UploadedFile $file, string $typeValue, ?string $replaceDocumentId = null): array
    {
        $this->validateFile($file);
        $type = DocumentType::tryFrom($typeValue);
        if (null === $type) {
            throw new BadRequestHttpException('Type de document invalide.');
        }

        $document = null;
        if (null !== $replaceDocumentId) {
            $document = $this->documentRepository->find($replaceDocumentId);
            if (!$document instanceof CandidateDocument || $document->getCandidate()->getId() !== $candidate->getId()) {
                throw new BadRequestHttpException('Document introuvable.');
            }
            if (DocumentStatus::VALIDATED === $document->getStatus()) {
                throw new BadRequestHttpException('Un document validé ne peut pas être remplacé.');
            }
            if ($document->getStoragePath()) {
                $this->storageService->delete($document->getStoragePath());
            }
            $document->incrementVersion();
        } else {
            foreach ($candidate->getDocuments() as $existing) {
                if ($existing->getType() === $type && DocumentStatus::VALIDATED !== $existing->getStatus()) {
                    $document = $existing;
                    if ($document->getStoragePath()) {
                        $this->storageService->delete($document->getStoragePath());
                    }
                    $document->incrementVersion();
                    break;
                }
            }
            if (null === $document) {
                $document = new CandidateDocument($candidate, $type, DocumentStatus::UPLOADED);
                $candidate->addDocument($document);
            }
        }

        $originalName = $file->getClientOriginalName() ?: 'file';
        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        $size = (int) $file->getSize();

        $storagePath = $this->storageService->store(
            $file,
            $candidate->getId()->toRfc4122(),
            $document->getId()->toRfc4122(),
        );
        $document->markUploaded($originalName, $mimeType, $size, $storagePath);
        $this->documentRepository->save($document);
        $this->timelineService->record($candidate, 'document.uploaded', 'Document téléversé : '.$type->label());

        return $this->serializeDocument($document);
    }

    public function delete(Candidate $candidate, string $documentId): void
    {
        $document = $this->findOwnedDocument($candidate, $documentId);
        if (DocumentStatus::VALIDATED === $document->getStatus()) {
            throw new BadRequestHttpException('Un document validé ne peut pas être supprimé.');
        }
        if ($document->getStoragePath()) {
            $this->storageService->delete($document->getStoragePath());
        }
        $this->entityManager->remove($document);
        $this->entityManager->flush();
        $this->timelineService->record($candidate, 'document.deleted', 'Document supprimé : '.$document->getType()->label());
    }

    public function getOwnedDocument(Candidate $candidate, string $documentId): CandidateDocument
    {
        return $this->findOwnedDocument($candidate, $documentId);
    }

    private function findOwnedDocument(Candidate $candidate, string $documentId): CandidateDocument
    {
        $document = $this->documentRepository->find($documentId);
        if (!$document instanceof CandidateDocument || $document->getCandidate()->getId() !== $candidate->getId()) {
            throw new BadRequestHttpException('Document introuvable.');
        }

        return $document;
    }

    private function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_SIZE) {
            throw new BadRequestHttpException('Fichier trop volumineux (max 10 Mo).');
        }
        $mime = $file->getClientMimeType() ?: '';
        if (!\in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new BadRequestHttpException('Type de fichier non autorisé.');
        }
    }

    private function isPreviewable(?string $mimeType): bool
    {
        if (null === $mimeType) {
            return false;
        }

        return str_starts_with($mimeType, 'image/') || 'application/pdf' === $mimeType;
    }
}
