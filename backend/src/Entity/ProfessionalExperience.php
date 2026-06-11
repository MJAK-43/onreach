<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProfessionalExperienceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProfessionalExperienceRepository::class)]
#[ORM\Table(name: 'professional_experiences')]
class ProfessionalExperience
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ProfessionalProfile::class, inversedBy: 'experiences')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ProfessionalProfile $professionalProfile = null;

    #[ORM\Column(length: 255)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $company;

    #[ORM\Column(length: 150)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $position;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['candidate:read', 'candidate:write'])]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $description = null;

    public function __construct(string $company, string $position, \DateTimeImmutable $startDate)
    {
        $this->id = Uuid::v7();
        $this->company = $company;
        $this->position = $position;
        $this->startDate = $startDate;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProfessionalProfile(): ?ProfessionalProfile
    {
        return $this->professionalProfile;
    }

    public function setProfessionalProfile(?ProfessionalProfile $professionalProfile): self
    {
        $this->professionalProfile = $professionalProfile;

        return $this;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

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
}
