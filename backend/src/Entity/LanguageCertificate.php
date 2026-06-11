<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\LanguageCertificateType;
use App\Repository\LanguageCertificateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LanguageCertificateRepository::class)]
#[ORM\Table(name: 'language_certificates')]
class LanguageCertificate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: LanguageProfile::class, inversedBy: 'certificates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?LanguageProfile $languageProfile = null;

    #[ORM\Column(enumType: LanguageCertificateType::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private LanguageCertificateType $type;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $score = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?\DateTimeImmutable $issueDate = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?\DateTimeImmutable $expirationDate = null;

    public function __construct(LanguageCertificateType $type)
    {
        $this->id = Uuid::v7();
        $this->type = $type;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getLanguageProfile(): ?LanguageProfile
    {
        return $this->languageProfile;
    }

    public function setLanguageProfile(?LanguageProfile $languageProfile): self
    {
        $this->languageProfile = $languageProfile;

        return $this;
    }

    public function getType(): LanguageCertificateType
    {
        return $this->type;
    }

    public function setType(LanguageCertificateType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(?string $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getIssueDate(): ?\DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function setIssueDate(?\DateTimeImmutable $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeImmutable $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
