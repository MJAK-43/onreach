<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PathwaySubStepTemplateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PathwaySubStepTemplateRepository::class)]
#[ORM\Table(name: 'pathway_sub_step_templates')]
class PathwaySubStepTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'subSteps')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private PathwayStageTemplate $stageTemplate;

    #[ORM\Column(length: 200)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column]
    private bool $required = true;

    #[ORM\Column(nullable: true)]
    private ?int $defaultDueOffsetDays = null;

    #[ORM\Column]
    private int $sortOrder;

    public function __construct(
        PathwayStageTemplate $stageTemplate,
        string $title,
        ?string $description,
        bool $required,
        ?int $defaultDueOffsetDays,
        int $sortOrder,
    ) {
        $this->id = Uuid::v7();
        $this->stageTemplate = $stageTemplate;
        $this->title = $title;
        $this->description = $description;
        $this->required = $required;
        $this->defaultDueOffsetDays = $defaultDueOffsetDays;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStageTemplate(): PathwayStageTemplate
    {
        return $this->stageTemplate;
    }

    public function setStageTemplate(PathwayStageTemplate $stageTemplate): self
    {
        $this->stageTemplate = $stageTemplate;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDefaultDueOffsetDays(): ?int
    {
        return $this->defaultDueOffsetDays;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
