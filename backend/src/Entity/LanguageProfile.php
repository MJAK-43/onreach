<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LanguageProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LanguageProfileRepository::class)]
#[ORM\Table(name: 'language_profiles')]
class LanguageProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'languageProfile', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $frenchLevel = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $englishLevel = null;

    /** @var list<array<string, string>>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?array $otherLanguages = null;

    /** @var Collection<int, LanguageCertificate> */
    #[ORM\OneToMany(mappedBy: 'languageProfile', targetEntity: LanguageCertificate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $certificates;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->certificates = new ArrayCollection();
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
        if ($candidate->getLanguageProfile() !== $this) {
            $candidate->setLanguageProfile($this);
        }

        return $this;
    }

    public function getFrenchLevel(): ?string
    {
        return $this->frenchLevel;
    }

    public function setFrenchLevel(?string $frenchLevel): self
    {
        $this->frenchLevel = $frenchLevel;

        return $this;
    }

    public function getEnglishLevel(): ?string
    {
        return $this->englishLevel;
    }

    public function setEnglishLevel(?string $englishLevel): self
    {
        $this->englishLevel = $englishLevel;

        return $this;
    }

    /** @return list<array<string, string>>|null */
    public function getOtherLanguages(): ?array
    {
        return $this->otherLanguages;
    }

    /** @param list<array<string, string>>|null $otherLanguages */
    public function setOtherLanguages(?array $otherLanguages): self
    {
        $this->otherLanguages = $otherLanguages;

        return $this;
    }

    /** @return Collection<int, LanguageCertificate> */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(LanguageCertificate $certificate): self
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setLanguageProfile($this);
        }

        return $this;
    }

    public function removeCertificate(LanguageCertificate $certificate): self
    {
        if ($this->certificates->removeElement($certificate)) {
            if ($certificate->getLanguageProfile() === $this) {
                $certificate->setLanguageProfile(null);
            }
        }

        return $this;
    }
}
