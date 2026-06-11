<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProfessionalProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProfessionalProfileRepository::class)]
#[ORM\Table(name: 'professional_profiles')]
class ProfessionalProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'professionalProfile', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $currentOccupation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?int $yearsExperience = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $professionalSummary = null;

    /** @var Collection<int, ProfessionalExperience> */
    #[ORM\OneToMany(mappedBy: 'professionalProfile', targetEntity: ProfessionalExperience::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['startDate' => 'DESC'])]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $experiences;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->experiences = new ArrayCollection();
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
        if ($candidate->getProfessionalProfile() !== $this) {
            $candidate->setProfessionalProfile($this);
        }

        return $this;
    }

    public function getCurrentOccupation(): ?string
    {
        return $this->currentOccupation;
    }

    public function setCurrentOccupation(?string $currentOccupation): self
    {
        $this->currentOccupation = $currentOccupation;

        return $this;
    }

    public function getYearsExperience(): ?int
    {
        return $this->yearsExperience;
    }

    public function setYearsExperience(?int $yearsExperience): self
    {
        $this->yearsExperience = $yearsExperience;

        return $this;
    }

    public function getProfessionalSummary(): ?string
    {
        return $this->professionalSummary;
    }

    public function setProfessionalSummary(?string $professionalSummary): self
    {
        $this->professionalSummary = $professionalSummary;

        return $this;
    }

    /** @return Collection<int, ProfessionalExperience> */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(ProfessionalExperience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences->add($experience);
            $experience->setProfessionalProfile($this);
        }

        return $this;
    }

    public function removeExperience(ProfessionalExperience $experience): self
    {
        if ($this->experiences->removeElement($experience)) {
            if ($experience->getProfessionalProfile() === $this) {
                $experience->setProfessionalProfile(null);
            }
        }

        return $this;
    }
}
