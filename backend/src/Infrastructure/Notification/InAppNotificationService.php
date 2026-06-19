<?php

declare(strict_types=1);

namespace App\Infrastructure\Notification;

use App\Entity\InAppNotification;
use App\Entity\User;
use App\Repository\InAppNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class InAppNotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InAppNotificationRepository $notificationRepository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function notify(
        User $recipient,
        string $type,
        string $title,
        string $message,
        ?string $linkUrl = null,
        ?array $metadata = null,
    ): InAppNotification {
        $notification = new InAppNotification($recipient, $type, $title, $message, $linkUrl, $metadata);
        $this->entityManager->persist($notification);

        return $notification;
    }

    /**
     * @return list<InAppNotification>
     */
    public function listForUser(User $user, int $limit = 30): array
    {
        return $this->notificationRepository->findRecentForUser($user, $limit);
    }

    public function countUnread(User $user): int
    {
        return $this->notificationRepository->countUnreadForUser($user);
    }

    public function markAsRead(InAppNotification $notification, User $user): InAppNotification
    {
        if ($notification->getRecipient()->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
            throw new \InvalidArgumentException('Notification introuvable.');
        }

        $notification->markAsRead();

        return $notification;
    }

    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsReadForUser($user);
    }
}
