<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CandidatePathwaySubStepRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidatePathwaySubStepRepository::class)]
#[ORM\Table(name: 'candidate_pathway_sub_steps')]
class CandidatePathwaySubStep
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'subSteps')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CandidatePathwayStage $candidateStage;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private PathwaySubStepTemplate $subStepTemplate;

    #[ORM\Column]
    private int $sortOrder;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dueReminderSentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $counselorValidatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $counselorValidatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $adminValidatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $adminValidatedBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        CandidatePathwayStage $candidateStage,
        PathwaySubStepTemplate $subStepTemplate,
        int $sortOrder,
        ?\DateTimeImmutable $dueDate = null,
    ) {
        $this->id = Uuid::v7();
        $this->candidateStage = $candidateStage;
        $this->subStepTemplate = $subStepTemplate;
        $this->sortOrder = $sortOrder;
        $this->dueDate = $dueDate;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidateStage(): CandidatePathwayStage
    {
        return $this->candidateStage;
    }

    public function setCandidateStage(CandidatePathwayStage $candidateStage): self
    {
        $this->candidateStage = $candidateStage;

        return $this;
    }

    public function getSubStepTemplate(): PathwaySubStepTemplate
    {
        return $this->subStepTemplate;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getDueReminderSentAt(): ?\DateTimeImmutable
    {
        return $this->dueReminderSentAt;
    }

    public function markDueReminderSent(): self
    {
        $this->dueReminderSentAt = new \DateTimeImmutable();
        $this->touch();

        return $this;
    }

    public function getCounselorValidatedAt(): ?\DateTimeImmutable
    {
        return $this->counselorValidatedAt;
    }

    public function getCounselorValidatedBy(): ?User
    {
        return $this->counselorValidatedBy;
    }

    public function getAdminValidatedAt(): ?\DateTimeImmutable
    {
        return $this->adminValidatedAt;
    }

    public function getAdminValidatedBy(): ?User
    {
        return $this->adminValidatedBy;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function validateByCounselor(User $user): self
    {
        $this->counselorValidatedAt = new \DateTimeImmutable();
        $this->counselorValidatedBy = $user;
        $this->touch();

        return $this;
    }

    public function validateByAdmin(User $user): self
    {
        $this->adminValidatedAt = new \DateTimeImmutable();
        $this->adminValidatedBy = $user;
        $this->touch();

        return $this;
    }

    public function clearValidation(): self
    {
        $this->counselorValidatedAt = null;
        $this->counselorValidatedBy = null;
        $this->adminValidatedAt = null;
        $this->adminValidatedBy = null;
        $this->touch();

        return $this;
    }

    public function clearCounselorValidation(): self
    {
        $this->counselorValidatedAt = null;
        $this->counselorValidatedBy = null;
        $this->touch();

        return $this;
    }

    public function clearAdminValidation(): self
    {
        $this->adminValidatedAt = null;
        $this->adminValidatedBy = null;
        $this->touch();

        return $this;
    }

    public function isValidated(bool $doubleValidationRequired): bool
    {
        if ($doubleValidationRequired) {
            return null !== $this->counselorValidatedAt && null !== $this->adminValidatedAt;
        }

        return null !== $this->counselorValidatedAt || null !== $this->adminValidatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
