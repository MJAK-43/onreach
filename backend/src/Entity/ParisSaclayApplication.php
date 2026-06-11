<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\ParisSaclayDegreeLevel;
use App\Repository\ParisSaclayApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ParisSaclayApplicationRepository::class)]
#[ORM\Table(name: 'paris_saclay_applications')]
class ParisSaclayApplication
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'parisSaclayApplication', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(enumType: ParisSaclayDegreeLevel::class, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?ParisSaclayDegreeLevel $degreeLevel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $researchProject = null;

    /** @var list<array<string, mixed>>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $publications = null;

    /** @var list<array<string, mixed>>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $internshipReports = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
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
        if ($candidate->getParisSaclayApplication() !== $this) {
            $candidate->setParisSaclayApplication($this);
        }

        return $this;
    }

    public function getDegreeLevel(): ?ParisSaclayDegreeLevel
    {
        return $this->degreeLevel;
    }

    public function setDegreeLevel(?ParisSaclayDegreeLevel $degreeLevel): self
    {
        $this->degreeLevel = $degreeLevel;

        return $this;
    }

    public function getResearchProject(): ?string
    {
        return $this->researchProject;
    }

    public function setResearchProject(?string $researchProject): self
    {
        $this->researchProject = $researchProject;

        return $this;
    }

    /** @return list<array<string, mixed>>|null */
    public function getPublications(): ?array
    {
        return $this->publications;
    }

    /** @param list<array<string, mixed>>|null $publications */
    public function setPublications(?array $publications): self
    {
        $this->publications = $publications;

        return $this;
    }

    /** @return list<array<string, mixed>>|null */
    public function getInternshipReports(): ?array
    {
        return $this->internshipReports;
    }

    /** @param list<array<string, mixed>>|null $internshipReports */
    public function setInternshipReports(?array $internshipReports): self
    {
        $this->internshipReports = $internshipReports;

        return $this;
    }
}
