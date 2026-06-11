<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CandidateNoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidateNoteRepository::class)]
#[ORM\Table(name: 'candidate_notes')]
class CandidateNote
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Candidate::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Candidate $candidate;

    #[ORM\Column(length: 200)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $content;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['candidate:read'])]
    private User $author;

    #[ORM\Column]
    #[Groups(['candidate:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(Candidate $candidate, User $author, string $title, string $content)
    {
        $this->id = Uuid::v7();
        $this->candidate = $candidate;
        $this->author = $author;
        $this->title = $title;
        $this->content = $content;
        $this->createdAt = new \DateTimeImmutable();
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

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
