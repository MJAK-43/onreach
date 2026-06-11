<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\DocumentType;
use App\Repository\ChecklistItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChecklistItemRepository::class)]
#[ORM\Table(name: 'checklist_items')]
class ChecklistItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: ChecklistTemplate::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ChecklistTemplate $template = null;

    #[ORM\Column(enumType: DocumentType::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private DocumentType $documentType;

    #[ORM\Column(length: 200)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $label;

    #[ORM\Column]
    #[Groups(['candidate:read', 'candidate:write'])]
    private bool $required = true;

    #[ORM\Column]
    #[Groups(['candidate:read', 'candidate:write'])]
    private int $sortOrder = 0;

    public function __construct(DocumentType $documentType, string $label, int $sortOrder = 0, bool $required = true)
    {
        $this->id = Uuid::v7();
        $this->documentType = $documentType;
        $this->label = $label;
        $this->sortOrder = $sortOrder;
        $this->required = $required;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTemplate(): ?ChecklistTemplate
    {
        return $this->template;
    }

    public function setTemplate(?ChecklistTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->documentType;
    }

    public function setDocumentType(DocumentType $documentType): self
    {
        $this->documentType = $documentType;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
