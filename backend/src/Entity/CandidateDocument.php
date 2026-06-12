<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Repository\CandidateDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidateDocumentRepository::class)]
#[ORM\Table(name: 'candidate_documents')]
class CandidateDocument
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(enumType: DocumentType::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private DocumentType $type;

    #[ORM\Column(enumType: DocumentStatus::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private DocumentStatus $status;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?int $size = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $storagePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read'])]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['candidate:read'])]
    private ?User $validatedBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $rejectionReason = null;

    #[ORM\Column]
    #[Groups(['candidate:read'])]
    private int $version = 1;

    public function __construct(Candidate $candidate, DocumentType $type, DocumentStatus $status = DocumentStatus::MISSING)
    {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->type = $type;
        $this->status = $status;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getType(): DocumentType
    {
        return $this->type;
    }

    public function setType(DocumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): DocumentStatus
    {
        return $this->status;
    }

    public function setStatus(DocumentStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isValidated(): bool
    {
        return DocumentStatus::VALIDATED === $this->status;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(?string $storagePath): self
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeImmutable $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getValidatedBy(): ?User
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?User $validatedBy): self
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): self
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    public function markUploaded(string $originalFilename, string $mimeType, int $size, string $storagePath): self
    {
        $this->originalFilename = $originalFilename;
        $this->filename = basename($storagePath);
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->storagePath = $storagePath;
        $this->uploadedAt = new \DateTimeImmutable();
        $this->status = DocumentStatus::UPLOADED;

        return $this;
    }

    public function markValidated(User $validator): self
    {
        $this->status = DocumentStatus::VALIDATED;
        $this->validatedAt = new \DateTimeImmutable();
        $this->validatedBy = $validator;
        $this->rejectionReason = null;

        return $this;
    }

    public function markRejected(string $reason): self
    {
        $this->status = DocumentStatus::REJECTED;
        $this->rejectionReason = $reason;
        $this->validatedAt = null;
        $this->validatedBy = null;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function incrementVersion(): self
    {
        ++$this->version;

        return $this;
    }
}
