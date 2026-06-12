<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GuarantorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GuarantorRepository::class)]
#[ORM\Table(name: 'guarantors')]
class Guarantor
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: FinancingProfile::class, inversedBy: 'guarantors')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FinancingProfile $financingProfile = null;

    #[ORM\Column(length: 150)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $fullName;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $profession = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $monthlyIncome = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ?string $employer = null;

    public function __construct(string $fullName)
    {
        $this->id = Uuid::v7();
        $this->fullName = $fullName;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFinancingProfile(): ?FinancingProfile
    {
        return $this->financingProfile;
    }

    public function setFinancingProfile(?FinancingProfile $financingProfile): self
    {
        $this->financingProfile = $financingProfile;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getMonthlyIncome(): ?string
    {
        return $this->monthlyIncome;
    }

    public function setMonthlyIncome(?string $monthlyIncome): self
    {
        $this->monthlyIncome = $monthlyIncome;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email ? strtolower($email) : null;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmployer(): ?string
    {
        return $this->employer;
    }

    public function setEmployer(?string $employer): self
    {
        $this->employer = $employer;

        return $this;
    }
}
