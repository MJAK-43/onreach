<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Entity\User;
use App\Infrastructure\Conversation\ConversationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me/conversations')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MeConversationController extends AbstractController
{
    public function __construct(
        private readonly ConversationService $conversationService,
    ) {
    }

    #[Route('', name: 'me_conversations_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->requireUser();

        return new JsonResponse([
            'items' => $this->conversationService->listForUser($user),
        ]);
    }

    #[Route('/open', name: 'me_conversations_open', methods: ['POST'], priority: 10)]
    public function open(Request $request): JsonResponse
    {
        $user = $this->requireUser();
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !isset($payload['body'])) {
            throw new BadRequestHttpException('Le champ body est requis.');
        }

        return new JsonResponse($this->conversationService->startForCandidate($user, (string) $payload['body']));
    }

    #[Route('/{id}', name: 'me_conversations_show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $user = $this->requireUser();

        return new JsonResponse($this->conversationService->getThread($id, $user));
    }

    #[Route('/{id}/messages', name: 'me_conversations_send', methods: ['POST'])]
    public function send(string $id, Request $request): JsonResponse
    {
        $user = $this->requireUser();
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !isset($payload['body'])) {
            throw new BadRequestHttpException('Le champ body est requis.');
        }

        return new JsonResponse($this->conversationService->sendMessage($id, $user, (string) $payload['body']));
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
