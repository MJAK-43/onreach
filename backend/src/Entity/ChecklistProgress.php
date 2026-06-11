<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChecklistProgressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChecklistProgressRepository::class)]
#[ORM\Table(name: 'checklist_progress')]
#[ORM\UniqueConstraint(name: 'uniq_checklist_progress_candidate_item', columns: ['candidate_id', 'checklist_item_id'])]
class ChecklistProgress
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\ManyToOne(targetEntity: ChecklistItem::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ChecklistItem $checklistItem;

    #[ORM\Column]
    #[Groups(['candidate:read', 'candidate:write'])]
    private bool $completed = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct(Candidate $candidate, ChecklistItem $checklistItem)
    {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->checklistItem = $checklistItem;
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

    public function getChecklistItem(): ChecklistItem
    {
        return $this->checklistItem;
    }

    public function setChecklistItem(ChecklistItem $checklistItem): self
    {
        $this->checklistItem = $checklistItem;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        if ($completed && null === $this->completedAt) {
            $this->completedAt = new \DateTimeImmutable();
        }
        if (!$completed) {
            $this->completedAt = null;
        }

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
