<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AuditTrailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuditTrailRepository::class)]
#[ORM\Table(name: 'audit_trails')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('system.logs')"),
        new Get(security: "is_granted('system.logs')"),
    ],
    normalizationContext: ['groups' => ['audit:read']],
    order: ['createdAt' => 'DESC'],
)]
class AuditTrail
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['audit:read'])]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['audit:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    #[Groups(['audit:read'])]
    private string $action;

    #[ORM\Column(length: 100)]
    #[Groups(['audit:read'])]
    private string $entityType;

    #[ORM\Column(length: 36, nullable: true)]
    #[Groups(['audit:read'])]
    private ?string $entityId = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['audit:read'])]
    private ?array $oldValue = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['audit:read'])]
    private ?array $newValue = null;

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['audit:read'])]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['audit:read'])]
    private ?string $userAgent = null;

    #[ORM\Column]
    #[Groups(['audit:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed>|null $oldValue
     * @param array<string, mixed>|null $newValue
     */
    public function __construct(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
    ) {
        $this->id = Uuid::v7();
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    /** @return array<string, mixed>|null */
    public function getOldValue(): ?array
    {
        return $this->oldValue;
    }

    /** @return array<string, mixed>|null */
    public function getNewValue(): ?array
    {
        return $this->newValue;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
