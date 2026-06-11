<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParcoursupWishRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ParcoursupWishRepository::class)]
#[ORM\Table(name: 'parcoursup_wishes')]
class ParcoursupWish
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ParcoursupApplication::class, inversedBy: 'wishes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ParcoursupApplication $parcoursupApplication = null;

    #[ORM\Column]
    #[Groups(['candidate:read', 'candidate:write'])]
    private int $rank;

    #[ORM\Column(length: 255)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $university;

    #[ORM\Column(length: 255)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $program;

    #[ORM\Column(length: 50)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $status;

    public function __construct(int $rank, string $university, string $program, string $status = 'pending')
    {
        $this->id = Uuid::v7();
        $this->rank = $rank;
        $this->university = $university;
        $this->program = $program;
        $this->status = $status;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getParcoursupApplication(): ?ParcoursupApplication
    {
        return $this->parcoursupApplication;
    }

    public function setParcoursupApplication(?ParcoursupApplication $parcoursupApplication): self
    {
        $this->parcoursupApplication = $parcoursupApplication;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getUniversity(): string
    {
        return $this->university;
    }

    public function setUniversity(string $university): self
    {
        $this->university = $university;

        return $this;
    }

    public function getProgram(): string
    {
        return $this->program;
    }

    public function setProgram(string $program): self
    {
        $this->program = $program;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
