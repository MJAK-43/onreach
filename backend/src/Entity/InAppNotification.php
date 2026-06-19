<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InAppNotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: InAppNotificationRepository::class)]
#[ORM\Table(name: 'in_app_notifications')]
#[ORM\Index(name: 'IDX_in_app_notifications_recipient_read', columns: ['recipient_id', 'read_at'])]
class InAppNotification
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $recipient;

    #[ORM\Column(length: 80)]
    private string $type;

    #[ORM\Column(length: 180)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkUrl;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        User $recipient,
        string $type,
        string $title,
        string $message,
        ?string $linkUrl = null,
        ?array $metadata = null,
    ) {
        $this->id = Uuid::v7();
        $this->recipient = $recipient;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->linkUrl = $linkUrl;
        $this->metadata = $metadata;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLinkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isRead(): bool
    {
        return null !== $this->readAt;
    }

    public function markAsRead(): self
    {
        if (null === $this->readAt) {
            $this->readAt = new \DateTimeImmutable();
        }

        return $this;
    }
}
