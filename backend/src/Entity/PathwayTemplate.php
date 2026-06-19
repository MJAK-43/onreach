<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\StudyApplicationType;
use App\Repository\PathwayTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PathwayTemplateRepository::class)]
#[ORM\Table(name: 'pathway_templates')]
#[ORM\UniqueConstraint(name: 'uniq_pathway_template_campaign_code', columns: ['campaign_id', 'code'])]
class PathwayTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'pathwayTemplates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Campaign $campaign;

    #[ORM\Column(enumType: PathwayCode::class)]
    private PathwayCode $code;

    #[ORM\Column(length: 150)]
    private string $name;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $eligibleStudyTypes = [];

    /** @var Collection<int, PathwayStageTemplate> */
    #[ORM\OneToMany(mappedBy: 'pathwayTemplate', targetEntity: PathwayStageTemplate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $stages;

    /**
     * @param list<StudyApplicationType> $eligibleStudyTypes
     */
    public function __construct(Campaign $campaign, PathwayCode $code, string $name, array $eligibleStudyTypes)
    {
        $this->id = Uuid::v7();
        $this->campaign = $campaign;
        $this->code = $code;
        $this->name = $name;
        $this->eligibleStudyTypes = array_map(
            static fn (StudyApplicationType $type): string => $type->value,
            $eligibleStudyTypes,
        );
        $this->stages = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getCode(): PathwayCode
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @return list<StudyApplicationType> */
    public function getEligibleStudyTypes(): array
    {
        return array_map(
            static fn (string $value): StudyApplicationType => StudyApplicationType::from($value),
            $this->eligibleStudyTypes,
        );
    }

    public function isEligibleFor(StudyApplicationType $type): bool
    {
        return \in_array($type->value, $this->eligibleStudyTypes, true);
    }

    /** @return Collection<int, PathwayStageTemplate> */
    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function addStage(PathwayStageTemplate $stage): self
    {
        if (!$this->stages->contains($stage)) {
            $this->stages->add($stage);
            $stage->setPathwayTemplate($this);
        }

        return $this;
    }
}
