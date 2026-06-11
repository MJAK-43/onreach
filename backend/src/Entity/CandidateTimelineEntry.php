<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CandidateTimelineEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidateTimelineEntryRepository::class)]
#[ORM\Table(name: 'candidate_timeline_entries')]
class CandidateTimelineEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'timelineEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 100)]
    #[Groups(['candidate:read'])]
    private string $action;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read'])]
    private ?string $description = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read'])]
    private ?array $metadata = null;

    #[ORM\Column]
    #[Groups(['candidate:read'])]
    private \DateTimeImmutable $occurredAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['candidate:read'])]
    private ?User $actor = null;

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        Candidate $candidate,
        string $action,
        ?string $description = null,
        ?array $metadata = null,
        ?User $actor = null,
    ) {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->action = $action;
        $this->description = $description;
        $this->metadata = $metadata;
        $this->occurredAt = new \DateTimeImmutable();
        $this->actor = $actor;
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

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /** @param array<string, mixed>|null $metadata */
    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(\DateTimeImmutable $occurredAt): self
    {
        $this->occurredAt = $occurredAt;

        return $this;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function setActor(?User $actor): self
    {
        $this->actor = $actor;

        return $this;
    }
}
