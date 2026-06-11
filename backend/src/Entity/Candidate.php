<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\Candidate\Enum\CandidateStatus;
use App\Infrastructure\ApiPlatform\CandidateProcessor;
use App\Infrastructure\ApiPlatform\CandidateProvider;
use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\Table(name: 'candidates')]
#[ORM\UniqueConstraint(name: 'uniq_candidate_reference', columns: ['reference_number'])]
#[ORM\UniqueConstraint(name: 'uniq_candidate_email', columns: ['email'])]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('candidates.view')",
            provider: CandidateProvider::class,
        ),
        new Get(
            security: "is_granted('candidates.view')",
            provider: CandidateProvider::class,
        ),
        new Post(security: "is_granted('candidates.create')", processor: CandidateProcessor::class),
        new Put(
            security: "is_granted('candidates.edit') and (user.hasRole('SUPER_ADMIN') or user.hasRole('ADMIN') or object.getAssignedCounselor() == user)",
            processor: CandidateProcessor::class,
        ),
        new Delete(
            security: "is_granted('candidates.delete') and (user.hasRole('SUPER_ADMIN') or user.hasRole('ADMIN'))",
            processor: CandidateProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['candidate:read']],
    denormalizationContext: ['groups' => ['candidate:write']],
    provider: CandidateProvider::class,
    processor: CandidateProcessor::class,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'status' => 'exact',
    'nationality' => 'ipartial',
    'country' => 'ipartial',
    'assignedCounselor.id' => 'exact',
])]
class Candidate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\Column(name: 'reference_number', length: 30)]
    #[Groups(['candidate:read'])]
    private string $referenceNumber;

    #[ORM\Column(length: 100)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $firstName;

    #[ORM\Column(length: 100)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $lastName;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $gender = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $placeOfBirth = null;

    #[ORM\Column(length: 100)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $nationality;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 180)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $country = null;

    #[ORM\Column(enumType: CandidateStatus::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private CandidateStatus $status;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?User $assignedCounselor = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: AcademicProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?AcademicProfile $academicProfile = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: LanguageProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?LanguageProfile $languageProfile = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: ProfessionalProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?ProfessionalProfile $professionalProfile = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: FinancingProfile::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?FinancingProfile $financingProfile = null;

    /** @var Collection<int, CandidateDocument> */
    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: CandidateDocument::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;

    /** @var Collection<int, CandidateNote> */
    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: CandidateNote::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $notes;

    /** @var Collection<int, CandidateTimelineEntry> */
    #[ORM\OneToMany(mappedBy: 'candidate', targetEntity: CandidateTimelineEntry::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['occurredAt' => 'DESC'])]
    private Collection $timelineEntries;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: CampusFranceApplication::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?CampusFranceApplication $campusFranceApplication = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: ParcoursupApplication::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?ParcoursupApplication $parcoursupApplication = null;

    #[ORM\OneToOne(mappedBy: 'candidate', targetEntity: ParisSaclayApplication::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?ParisSaclayApplication $parisSaclayApplication = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $firstName, string $lastName, string $email, string $nationality, string $referenceNumber = 'PENDING')
    {
        $this->id = Uuid::v7();
        $this->referenceNumber = $referenceNumber;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = strtolower($email);
        $this->nationality = $nationality;
        $this->status = CandidateStatus::LEAD;
        $this->documents = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->timelineEntries = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getReferenceNumber(): string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        $this->touch();

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        $this->touch();

        return $this;
    }

    #[Groups(['candidate:read'])]
    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;
        $this->touch();

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeImmutable $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;
        $this->touch();

        return $this;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(?string $placeOfBirth): self
    {
        $this->placeOfBirth = $placeOfBirth;
        $this->touch();

        return $this;
    }

    public function getNationality(): string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): self
    {
        $this->nationality = $nationality;
        $this->touch();

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        $this->touch();

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);
        $this->touch();

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        $this->touch();

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        $this->touch();

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        $this->touch();

        return $this;
    }

    public function getStatus(): CandidateStatus
    {
        return $this->status;
    }

    public function setStatus(CandidateStatus $status): self
    {
        $this->status = $status;
        $this->touch();

        return $this;
    }

    public function getAssignedCounselor(): ?User
    {
        return $this->assignedCounselor;
    }

    public function setAssignedCounselor(?User $assignedCounselor): self
    {
        $this->assignedCounselor = $assignedCounselor;
        $this->touch();

        return $this;
    }

    public function getAcademicProfile(): ?AcademicProfile
    {
        return $this->academicProfile;
    }

    public function setAcademicProfile(?AcademicProfile $academicProfile): self
    {
        if (null !== $academicProfile && $academicProfile->getCandidate() !== $this) {
            $academicProfile->setCandidate($this);
        }
        $this->academicProfile = $academicProfile;
        $this->touch();

        return $this;
    }

    public function getLanguageProfile(): ?LanguageProfile
    {
        return $this->languageProfile;
    }

    public function setLanguageProfile(?LanguageProfile $languageProfile): self
    {
        if (null !== $languageProfile && $languageProfile->getCandidate() !== $this) {
            $languageProfile->setCandidate($this);
        }
        $this->languageProfile = $languageProfile;
        $this->touch();

        return $this;
    }

    public function getProfessionalProfile(): ?ProfessionalProfile
    {
        return $this->professionalProfile;
    }

    public function setProfessionalProfile(?ProfessionalProfile $professionalProfile): self
    {
        if (null !== $professionalProfile && $professionalProfile->getCandidate() !== $this) {
            $professionalProfile->setCandidate($this);
        }
        $this->professionalProfile = $professionalProfile;
        $this->touch();

        return $this;
    }

    public function getFinancingProfile(): ?FinancingProfile
    {
        return $this->financingProfile;
    }

    public function setFinancingProfile(?FinancingProfile $financingProfile): self
    {
        if (null !== $financingProfile && $financingProfile->getCandidate() !== $this) {
            $financingProfile->setCandidate($this);
        }
        $this->financingProfile = $financingProfile;
        $this->touch();

        return $this;
    }

    /** @return Collection<int, CandidateDocument> */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(CandidateDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCandidate($this);
        }

        return $this;
    }

    /** @return Collection<int, CandidateNote> */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    /** @return Collection<int, CandidateTimelineEntry> */
    public function getTimelineEntries(): Collection
    {
        return $this->timelineEntries;
    }

    public function getCampusFranceApplication(): ?CampusFranceApplication
    {
        return $this->campusFranceApplication;
    }

    public function setCampusFranceApplication(?CampusFranceApplication $application): self
    {
        $this->campusFranceApplication = $application;
        if (null !== $application) {
            $application->setCandidate($this);
        }
        $this->touch();

        return $this;
    }

    public function getParcoursupApplication(): ?ParcoursupApplication
    {
        return $this->parcoursupApplication;
    }

    public function setParcoursupApplication(?ParcoursupApplication $application): self
    {
        if (null !== $application && $application->getCandidate() !== $this) {
            $application->setCandidate($this);
        }
        $this->parcoursupApplication = $application;
        $this->touch();

        return $this;
    }

    public function getParisSaclayApplication(): ?ParisSaclayApplication
    {
        return $this->parisSaclayApplication;
    }

    public function setParisSaclayApplication(?ParisSaclayApplication $application): self
    {
        if (null !== $application && $application->getCandidate() !== $this) {
            $application->setCandidate($this);
        }
        $this->parisSaclayApplication = $application;
        $this->touch();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[Groups(['candidate:read'])]
    public function getCompletionPercent(): int
    {
        $sections = [
            $this->isIdentityComplete(),
            null !== $this->academicProfile,
            null !== $this->languageProfile,
            null !== $this->financingProfile,
            $this->hasValidatedDocument(),
            null !== $this->campusFranceApplication,
        ];
        $done = count(array_filter($sections));

        return (int) round(($done / count($sections)) * 100);
    }

    private function isIdentityComplete(): bool
    {
        return null !== $this->dateOfBirth && null !== $this->phone && null !== $this->country;
    }

    private function hasValidatedDocument(): bool
    {
        foreach ($this->documents as $document) {
            if ($document->isValidated()) {
                return true;
            }
        }

        return false;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
