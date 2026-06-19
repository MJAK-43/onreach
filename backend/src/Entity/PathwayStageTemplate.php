<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PathwayStageTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PathwayStageTemplateRepository::class)]
#[ORM\Table(name: 'pathway_stage_templates')]
class PathwayStageTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'stages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PathwayTemplate $pathwayTemplate;

    #[ORM\Column(length: 200)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column]
    private int $sortOrder;

    /** @var Collection<int, PathwaySubStepTemplate> */
    #[ORM\OneToMany(mappedBy: 'stageTemplate', targetEntity: PathwaySubStepTemplate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $subSteps;

    public function __construct(PathwayTemplate $pathwayTemplate, string $title, ?string $description, int $sortOrder)
    {
        $this->id = Uuid::v7();
        $this->pathwayTemplate = $pathwayTemplate;
        $this->title = $title;
        $this->description = $description;
        $this->sortOrder = $sortOrder;
        $this->subSteps = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPathwayTemplate(): PathwayTemplate
    {
        return $this->pathwayTemplate;
    }

    public function setPathwayTemplate(PathwayTemplate $pathwayTemplate): self
    {
        $this->pathwayTemplate = $pathwayTemplate;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /** @return Collection<int, PathwaySubStepTemplate> */
    public function getSubSteps(): Collection
    {
        return $this->subSteps;
    }

    public function addSubStep(PathwaySubStepTemplate $subStep): self
    {
        if (!$this->subSteps->contains($subStep)) {
            $this->subSteps->add($subStep);
            $subStep->setStageTemplate($this);
        }

        return $this;
    }
}
