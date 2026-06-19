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

#[Route('/api/candidates')]
final class CandidateConversationController extends AbstractController
{
    public function __construct(
        private readonly ConversationService $conversationService,
    ) {
    }

    #[Route('/{id}/conversation/messages', name: 'candidate_conversation_send', methods: ['POST'])]
    #[IsGranted('applications.view')]
    public function send(string $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload) || !isset($payload['body'])) {
            throw new BadRequestHttpException('Le champ body est requis.');
        }

        return new JsonResponse($this->conversationService->startForStaff($user, $id, (string) $payload['body']));
    }
}
