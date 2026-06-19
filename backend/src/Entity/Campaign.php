<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\Table(name: 'campaigns')]
class Campaign
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column]
    private int $year;

    #[ORM\Column]
    private \DateTimeImmutable $startDate;

    #[ORM\Column]
    private \DateTimeImmutable $endDate;

    #[ORM\Column]
    private bool $active = false;

    /** @var Collection<int, PathwayTemplate> */
    #[ORM\OneToMany(mappedBy: 'campaign', targetEntity: PathwayTemplate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $pathwayTemplates;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, int $year, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate)
    {
        $this->id = Uuid::v7();
        $this->name = $name;
        $this->year = $year;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->pathwayTemplates = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /** @return Collection<int, PathwayTemplate> */
    public function getPathwayTemplates(): Collection
    {
        return $this->pathwayTemplates;
    }

    public function addPathwayTemplate(PathwayTemplate $template): self
    {
        if (!$this->pathwayTemplates->contains($template)) {
            $this->pathwayTemplates->add($template);
            $template->setCampaign($this);
        }

        return $this;
    }
}
