<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Domain\Security\SecurityEventType;
use App\Repository\SecurityLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SecurityLogRepository::class)]
#[ORM\Table(name: 'security_logs')]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('system.logs')"),
    ],
    normalizationContext: ['groups' => ['security_log:read']],
    order: ['createdAt' => 'DESC'],
)]
class SecurityLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['security_log:read'])]
    private Uuid $id;

    #[ORM\Column(length: 50, enumType: SecurityEventType::class)]
    #[Groups(['security_log:read'])]
    private SecurityEventType $event;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['security_log:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['security_log:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['security_log:read'])]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['security_log:read'])]
    private ?string $userAgent = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['security_log:read'])]
    private ?array $metadata = null;

    #[ORM\Column]
    #[Groups(['security_log:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(SecurityEventType $event)
    {
        $this->id = Uuid::v7();
        $this->event = $event;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEvent(): SecurityEventType
    {
        return $this->event;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
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

    /** @return array<string, mixed>|null */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /** @param array<string, mixed>|null $metadata */
    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
