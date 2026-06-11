<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\CampusFranceStatus;
use App\Repository\CampusFranceApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CampusFranceApplicationRepository::class)]
#[ORM\Table(name: 'campus_france_applications')]
class CampusFranceApplication
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'campusFranceApplication', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $studyProject = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $professionalProject = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $targetUniversities = null;

    /** @var list<string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $targetPrograms = null;

    #[ORM\Column(enumType: CampusFranceStatus::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private CampusFranceStatus $status;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->status = CampusFranceStatus::DRAFT;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate ?? null;
    }

    public function setCandidate(Candidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getStudyProject(): ?string
    {
        return $this->studyProject;
    }

    public function setStudyProject(?string $studyProject): self
    {
        $this->studyProject = $studyProject;

        return $this;
    }

    public function getProfessionalProject(): ?string
    {
        return $this->professionalProject;
    }

    public function setProfessionalProject(?string $professionalProject): self
    {
        $this->professionalProject = $professionalProject;

        return $this;
    }

    /** @return list<string>|null */
    public function getTargetUniversities(): ?array
    {
        return $this->targetUniversities;
    }

    /** @param list<string>|null $targetUniversities */
    public function setTargetUniversities(?array $targetUniversities): self
    {
        $this->targetUniversities = $targetUniversities;

        return $this;
    }

    /** @return list<string>|null */
    public function getTargetPrograms(): ?array
    {
        return $this->targetPrograms;
    }

    /** @param list<string>|null $targetPrograms */
    public function setTargetPrograms(?array $targetPrograms): self
    {
        $this->targetPrograms = $targetPrograms;

        return $this;
    }

    public function getStatus(): CampusFranceStatus
    {
        return $this->status;
    }

    public function setStatus(CampusFranceStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
