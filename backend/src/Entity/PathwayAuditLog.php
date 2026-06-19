<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PathwayAuditLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PathwayAuditLogRepository::class)]
#[ORM\Table(name: 'pathway_audit_logs')]
class PathwayAuditLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CandidatePathway $candidatePathway;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CandidatePathwaySubStep $candidateSubStep;

    #[ORM\Column(length: 80)]
    private string $action;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $payload = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $performedBy;

    #[ORM\Column]
    private \DateTimeImmutable $occurredAt;

    /**
     * @param array<string, mixed>|null $payload
     */
    public function __construct(
        Candidate $candidate,
        string $action,
        ?CandidatePathway $candidatePathway = null,
        ?CandidatePathwaySubStep $candidateSubStep = null,
        ?array $payload = null,
        ?User $performedBy = null,
    ) {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->candidatePathway = $candidatePathway;
        $this->candidateSubStep = $candidateSubStep;
        $this->action = $action;
        $this->payload = $payload;
        $this->performedBy = $performedBy;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /** @return array<string, mixed>|null */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getPerformedBy(): ?User
    {
        return $this->performedBy;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getCandidatePathway(): ?CandidatePathway
    {
        return $this->candidatePathway;
    }

    public function getCandidateSubStep(): ?CandidatePathwaySubStep
    {
        return $this->candidateSubStep;
    }
}
