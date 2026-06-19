<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Entity\User;
use App\Infrastructure\Notification\InAppNotificationService;
use App\Repository\InAppNotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/me/notifications')]
final class MeNotificationsController extends AbstractController
{
    public function __construct(
        private readonly InAppNotificationService $notificationService,
        private readonly InAppNotificationRepository $notificationRepository,
    ) {
    }

    #[Route('', name: 'me_notifications_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->requireUser();
        $notifications = $this->notificationService->listForUser($user);

        return new JsonResponse([
            'unreadCount' => $this->notificationService->countUnread($user),
            'items' => array_map(static fn ($notification) => [
                'id' => $notification->getId()->toRfc4122(),
                'type' => $notification->getType(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'linkUrl' => $notification->getLinkUrl(),
                'metadata' => $notification->getMetadata(),
                'read' => $notification->isRead(),
                'readAt' => $notification->getReadAt()?->format(\DateTimeInterface::ATOM),
                'createdAt' => $notification->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ], $notifications),
        ]);
    }

    #[Route('/read-all', name: 'me_notifications_read_all', methods: ['POST'])]
    public function readAll(): JsonResponse
    {
        $user = $this->requireUser();
        $count = $this->notificationService->markAllAsRead($user);
        $this->notificationRepository->getEntityManager()->flush();

        return new JsonResponse(['marked' => $count]);
    }

    #[Route('/{id}/read', name: 'me_notifications_read_one', methods: ['PATCH'])]
    public function readOne(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            throw new NotFoundHttpException('Notification introuvable.');
        }

        $user = $this->requireUser();
        $notification = $this->notificationRepository->find(Uuid::fromString($id));
        if (!$notification) {
            throw new NotFoundHttpException('Notification introuvable.');
        }

        $this->notificationService->markAsRead($notification, $user);
        $this->notificationRepository->getEntityManager()->flush();

        return new JsonResponse(['read' => true]);
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
