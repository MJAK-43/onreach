<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\FinancingType;
use App\Repository\FinancingProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FinancingProfileRepository::class)]
#[ORM\Table(name: 'financing_profiles')]
class FinancingProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'financingProfile', targetEntity: Candidate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(enumType: FinancingType::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private FinancingType $type;

    /** @var Collection<int, Guarantor> */
    #[ORM\OneToMany(mappedBy: 'financingProfile', targetEntity: Guarantor::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $guarantors;

    public function __construct(FinancingType $type = FinancingType::SELF_FUNDED)
    {
        $this->id = Uuid::v7();
        $this->type = $type;
        $this->guarantors = new ArrayCollection();
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
        if ($candidate->getFinancingProfile() !== $this) {
            $candidate->setFinancingProfile($this);
        }

        return $this;
    }

    public function getType(): FinancingType
    {
        return $this->type;
    }

    public function setType(FinancingType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** @return Collection<int, Guarantor> */
    public function getGuarantors(): Collection
    {
        return $this->guarantors;
    }

    public function addGuarantor(Guarantor $guarantor): self
    {
        if (!$this->guarantors->contains($guarantor)) {
            $this->guarantors->add($guarantor);
            $guarantor->setFinancingProfile($this);
        }

        return $this;
    }

    public function removeGuarantor(Guarantor $guarantor): self
    {
        if ($this->guarantors->removeElement($guarantor)) {
            if ($guarantor->getFinancingProfile() === $this) {
                $guarantor->setFinancingProfile(null);
            }
        }

        return $this;
    }
}
