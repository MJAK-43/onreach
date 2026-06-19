<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Repository\CandidatePathwayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidatePathwayRepository::class)]
#[ORM\Table(name: 'candidate_pathways')]
#[ORM\UniqueConstraint(name: 'uniq_candidate_pathway', columns: ['candidate_id', 'pathway_template_id'])]
class CandidatePathway
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'pathways')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private PathwayTemplate $pathwayTemplate;

    #[ORM\Column(enumType: PathwayInstanceStatus::class)]
    private PathwayInstanceStatus $status = PathwayInstanceStatus::NOT_STARTED;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $blockedReason = null;

    #[ORM\Column]
    private int $progressPercent = 0;

    /** @var Collection<int, CandidatePathwayStage> */
    #[ORM\OneToMany(mappedBy: 'candidatePathway', targetEntity: CandidatePathwayStage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $stages;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Candidate $candidate, PathwayTemplate $pathwayTemplate)
    {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->pathwayTemplate = $pathwayTemplate;
        $this->stages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getPathwayTemplate(): PathwayTemplate
    {
        return $this->pathwayTemplate;
    }

    public function getStatus(): PathwayInstanceStatus
    {
        return $this->status;
    }

    public function setStatus(PathwayInstanceStatus $status): self
    {
        $this->status = $status;
        $this->touch();

        return $this;
    }

    public function getBlockedReason(): ?string
    {
        return $this->blockedReason;
    }

    public function setBlockedReason(?string $blockedReason): self
    {
        $this->blockedReason = $blockedReason;
        $this->touch();

        return $this;
    }

    public function getProgressPercent(): int
    {
        return $this->progressPercent;
    }

    public function setProgressPercent(int $progressPercent): self
    {
        $this->progressPercent = max(0, min(100, $progressPercent));
        $this->touch();

        return $this;
    }

    /** @return Collection<int, CandidatePathwayStage> */
    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function addStage(CandidatePathwayStage $stage): self
    {
        if (!$this->stages->contains($stage)) {
            $this->stages->add($stage);
            $stage->setCandidatePathway($this);
        }

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
