<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Infrastructure\ApiPlatform\PermissionProcessor;
use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions')]
#[ORM\UniqueConstraint(name: 'uniq_permission_code', columns: ['code'])]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('permissions.view')"),
        new Get(security: "is_granted('permissions.view')"),
        new Patch(security: "is_granted('permissions.edit')"),
    ],
    normalizationContext: ['groups' => ['permission:read']],
    denormalizationContext: ['groups' => ['permission:write']],
    processor: PermissionProcessor::class,
)]
class Permission
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['permission:read', 'role:read', 'user:read'])]
    private Uuid $id;

    #[ORM\Column(length: 100)]
    #[Groups(['permission:read', 'role:read', 'user:read'])]
    private string $code;

    #[ORM\Column(length: 150)]
    #[Groups(['permission:read', 'permission:write'])]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['permission:read', 'permission:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['permission:read'])]
    private bool $isSystem = false;

    /** @var Collection<int, Role> */
    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'permissions')]
    private Collection $roles;

    public function __construct(string $code, string $name, bool $isSystem = false)
    {
        $this->id = Uuid::v7();
        $this->code = $code;
        $this->name = $name;
        $this->isSystem = $isSystem;
        $this->roles = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    /** @return Collection<int, Role> */
    public function getRoles(): Collection
    {
        return $this->roles;
    }
}
