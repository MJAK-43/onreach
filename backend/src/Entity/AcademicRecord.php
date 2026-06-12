<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AcademicRecordRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AcademicRecordRepository::class)]
#[ORM\Table(name: 'academic_records')]
class AcademicRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: AcademicProfile::class, inversedBy: 'records')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AcademicProfile $academicProfile = null;

    #[ORM\Column(length: 150)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $diploma;

    #[ORM\Column(length: 255)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $institution;

    #[ORM\Column]
    #[Groups(['candidate:read', 'candidate:write'])]
    private int $year;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $average = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $ranking = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $specialty = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $achievements = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $diplomaType = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $country = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $mention = null;

    public function __construct(string $diploma, string $institution, int $year)
    {
        $this->id = Uuid::v7();
        $this->diploma = $diploma;
        $this->institution = $institution;
        $this->year = $year;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAcademicProfile(): ?AcademicProfile
    {
        return $this->academicProfile;
    }

    public function setAcademicProfile(?AcademicProfile $academicProfile): self
    {
        $this->academicProfile = $academicProfile;

        return $this;
    }

    public function getDiploma(): string
    {
        return $this->diploma;
    }

    public function setDiploma(string $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getAverage(): ?string
    {
        return $this->average;
    }

    public function setAverage(?string $average): self
    {
        $this->average = $average;

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

    public function getAchievements(): ?string
    {
        return $this->achievements;
    }

    public function setAchievements(?string $achievements): self
    {
        $this->achievements = $achievements;

        return $this;
    }

    public function getDiplomaType(): ?string
    {
        return $this->diplomaType;
    }

    public function setDiplomaType(?string $diplomaType): self
    {
        $this->diplomaType = $diplomaType;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getMention(): ?string
    {
        return $this->mention;
    }

    public function setMention(?string $mention): self
    {
        $this->mention = $mention;

        return $this;
    }
}
