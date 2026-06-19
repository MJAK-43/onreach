<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CandidatePathwayStageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidatePathwayStageRepository::class)]
#[ORM\Table(name: 'candidate_pathway_stages')]
class CandidatePathwayStage
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'stages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CandidatePathway $candidatePathway;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private PathwayStageTemplate $stageTemplate;

    #[ORM\Column]
    private int $sortOrder;

    #[ORM\Column]
    private int $progressPercent = 0;

    /** @var Collection<int, CandidatePathwaySubStep> */
    #[ORM\OneToMany(mappedBy: 'candidateStage', targetEntity: CandidatePathwaySubStep::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $subSteps;

    public function __construct(CandidatePathway $candidatePathway, PathwayStageTemplate $stageTemplate, int $sortOrder)
    {
        $this->id = Uuid::v7();
        $this->candidatePathway = $candidatePathway;
        $this->stageTemplate = $stageTemplate;
        $this->sortOrder = $sortOrder;
        $this->subSteps = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidatePathway(): CandidatePathway
    {
        return $this->candidatePathway;
    }

    public function setCandidatePathway(CandidatePathway $candidatePathway): self
    {
        $this->candidatePathway = $candidatePathway;

        return $this;
    }

    public function getStageTemplate(): PathwayStageTemplate
    {
        return $this->stageTemplate;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getProgressPercent(): int
    {
        return $this->progressPercent;
    }

    public function setProgressPercent(int $progressPercent): self
    {
        $this->progressPercent = max(0, min(100, $progressPercent));

        return $this;
    }

    /** @return Collection<int, CandidatePathwaySubStep> */
    public function getSubSteps(): Collection
    {
        return $this->subSteps;
    }

    public function addSubStep(CandidatePathwaySubStep $subStep): self
    {
        if (!$this->subSteps->contains($subStep)) {
            $this->subSteps->add($subStep);
            $subStep->setCandidateStage($this);
        }

        return $this;
    }
}
