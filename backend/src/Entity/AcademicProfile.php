<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AcademicProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AcademicProfileRepository::class)]
#[ORM\Table(name: 'academic_profiles')]
class AcademicProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'academicProfile', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $highestDiploma = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $institutionName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?int $graduationYear = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $overallAverage = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $ranking = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $specialty = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $academicAchievements = null;

    /** @var Collection<int, AcademicRecord> */
    #[ORM\OneToMany(mappedBy: 'academicProfile', targetEntity: AcademicRecord::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['year' => 'DESC'])]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $records;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->records = new ArrayCollection();
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
        if ($candidate->getAcademicProfile() !== $this) {
            $candidate->setAcademicProfile($this);
        }

        return $this;
    }

    public function getHighestDiploma(): ?string
    {
        return $this->highestDiploma;
    }

    public function setHighestDiploma(?string $highestDiploma): self
    {
        $this->highestDiploma = $highestDiploma;

        return $this;
    }

    public function getInstitutionName(): ?string
    {
        return $this->institutionName;
    }

    public function setInstitutionName(?string $institutionName): self
    {
        $this->institutionName = $institutionName;

        return $this;
    }

    public function getGraduationYear(): ?int
    {
        return $this->graduationYear;
    }

    public function setGraduationYear(?int $graduationYear): self
    {
        $this->graduationYear = $graduationYear;

        return $this;
    }

    public function getOverallAverage(): ?string
    {
        return $this->overallAverage;
    }

    public function setOverallAverage(?string $overallAverage): self
    {
        $this->overallAverage = $overallAverage;

        return $this;
    }

    public function getRanking(): ?string
    {
        return $this->ranking;
    }

    public function setRanking(?string $ranking): self
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getAcademicAchievements(): ?string
    {
        return $this->academicAchievements;
    }

    public function setAcademicAchievements(?string $academicAchievements): self
    {
        $this->academicAchievements = $academicAchievements;

        return $this;
    }

    /** @return Collection<int, AcademicRecord> */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(AcademicRecord $record): self
    {
        if (!$this->records->contains($record)) {
            $this->records->add($record);
            $record->setAcademicProfile($this);
        }

        return $this;
    }

    public function removeRecord(AcademicRecord $record): self
    {
        if ($this->records->removeElement($record)) {
            if ($record->getAcademicProfile() === $this) {
                $record->setAcademicProfile(null);
            }
        }

        return $this;
    }
}
