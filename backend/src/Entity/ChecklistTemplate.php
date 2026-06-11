<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Repository\ChecklistTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChecklistTemplateRepository::class)]
#[ORM\Table(name: 'checklist_templates')]
#[ORM\UniqueConstraint(name: 'uniq_checklist_template_code', columns: ['code'])]
class ChecklistTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['candidate:read'])]
    private Uuid $id;

    #[ORM\Column(length: 50)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $code;

    #[ORM\Column(length: 150)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private string $name;

    #[ORM\Column(enumType: ApplicationType::class)]
    #[Groups(['candidate:read', 'candidate:write'])]
    private ApplicationType $applicationType;

    /** @var Collection<int, ChecklistItem> */
    #[ORM\OneToMany(mappedBy: 'template', targetEntity: ChecklistItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    #[Groups(['candidate:read', 'candidate:write'])]
    private Collection $items;

    public function __construct(string $code, string $name, ApplicationType $applicationType)
    {
        $this->id = Uuid::v7();
        $this->code = $code;
        $this->name = $name;
        $this->applicationType = $applicationType;
        $this->items = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApplicationType(): ApplicationType
    {
        return $this->applicationType;
    }

    public function setApplicationType(ApplicationType $applicationType): self
    {
        $this->applicationType = $applicationType;

        return $this;
    }

    /** @return Collection<int, ChecklistItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ChecklistItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setTemplate($this);
        }

        return $this;
    }

    public function removeItem(ChecklistItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getTemplate() === $this) {
                $item->setTemplate(null);
            }
        }

        return $this;
    }
}
