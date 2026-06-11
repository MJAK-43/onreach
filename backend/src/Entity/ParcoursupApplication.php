<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParcoursupApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ParcoursupApplicationRepository::class)]
#[ORM\Table(name: 'parcoursup_applications')]
class ParcoursupApplication
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'parcoursupApplication', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $ineNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $highSchool = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $specialties = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $activities = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $interests = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $motivationProject = null;

    /** @var Collection<int, ParcoursupWish> */
    #[ORM\OneToMany(mappedBy: 'parcoursupApplication', targetEntity: ParcoursupWish::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['rank' => 'ASC'])]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $wishes;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->wishes = new ArrayCollection();
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
        if ($candidate->getParcoursupApplication() !== $this) {
            $candidate->setParcoursupApplication($this);
        }

        return $this;
    }

    public function getIneNumber(): ?string
    {
        return $this->ineNumber;
    }

    public function setIneNumber(?string $ineNumber): self
    {
        $this->ineNumber = $ineNumber;

        return $this;
    }

    public function getHighSchool(): ?string
    {
        return $this->highSchool;
    }

    public function setHighSchool(?string $highSchool): self
    {
        $this->highSchool = $highSchool;

        return $this;
    }

    /** @return list<string>|null */
    public function getSpecialties(): ?array
    {
        return $this->specialties;
    }

    /** @param list<string>|null $specialties */
    public function setSpecialties(?array $specialties): self
    {
        $this->specialties = $specialties;

        return $this;
    }

    public function getActivities(): ?string
    {
        return $this->activities;
    }

    public function setActivities(?string $activities): self
    {
        $this->activities = $activities;

        return $this;
    }

    public function getInterests(): ?string
    {
        return $this->interests;
    }

    public function setInterests(?string $interests): self
    {
        $this->interests = $interests;

        return $this;
    }

    public function getMotivationProject(): ?string
    {
        return $this->motivationProject;
    }

    public function setMotivationProject(?string $motivationProject): self
    {
        $this->motivationProject = $motivationProject;

        return $this;
    }

    /** @return Collection<int, ParcoursupWish> */
    public function getWishes(): Collection
    {
        return $this->wishes;
    }

    public function addWish(ParcoursupWish $wish): self
    {
        if (!$this->wishes->contains($wish)) {
            $this->wishes->add($wish);
            $wish->setParcoursupApplication($this);
        }

        return $this;
    }

    public function removeWish(ParcoursupWish $wish): self
    {
        if ($this->wishes->removeElement($wish)) {
            if ($wish->getParcoursupApplication() === $this) {
                $wish->setParcoursupApplication(null);
            }
        }

        return $this;
    }
}
