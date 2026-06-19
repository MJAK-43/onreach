<?php

declare(strict_types=1);

namespace App\Infrastructure\Conversation;

use App\Entity\Candidate;
use App\Entity\Conversation;
use App\Entity\ConversationMessage;
use App\Entity\User;
use App\Repository\CandidateRepository;
use App\Repository\ConversationMessageRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class ConversationService
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private ConversationMessageRepository $messageRepository,
        private CandidateRepository $candidateRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(User $user): array
    {
        $conversations = $this->conversationRepository->findForUser($user);

        return array_map(fn (Conversation $conversation) => $this->serializeConversation($conversation, $user), $conversations);
    }

    /**
     * @return array{conversation: array<string, mixed>, messages: list<array<string, mixed>>}
     */
    public function getThread(string $conversationId, User $user): array
    {
        $conversation = $this->loadConversation($conversationId);
        $this->assertCanAccess($conversation, $user);

        $messages = [];
        foreach ($conversation->getMessages() as $message) {
            if (null === $message->getReadAt() && $message->getAuthor()->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
                $message->markRead();
            }
            $messages[] = $this->serializeMessage($message);
        }
        $this->entityManager->flush();

        return [
            'conversation' => $this->serializeConversation($conversation, $user),
            'messages' => $messages,
        ];
    }

    /**
     * @return array{conversation: array<string, mixed>, message: array<string, mixed>}
     */
    public function sendMessage(string $conversationId, User $user, string $body): array
    {
        $body = trim($body);
        if ('' === $body) {
            throw new BadRequestHttpException('Le message ne peut pas être vide.');
        }

        $conversation = $this->loadConversation($conversationId);
        $this->assertCanAccess($conversation, $user);

        $message = new ConversationMessage($conversation, $user, $body);
        $conversation->addMessage($message);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return [
            'conversation' => $this->serializeConversation($conversation, $user),
            'message' => $this->serializeMessage($message),
        ];
    }

    /**
     * @return array{conversation: array<string, mixed>, message: array<string, mixed>}
     */
    public function startForCandidate(User $candidateUser, string $body): array
    {
        $candidate = $this->candidateRepository->findOneByEmailWithCounselorAndDocuments($candidateUser->getEmail());
        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Candidat introuvable.');
        }

        $counselor = $candidate->getAssignedCounselor();
        if (!$counselor) {
            throw new BadRequestHttpException('Aucun conseiller assigné.');
        }

        $conversation = $this->conversationRepository->findOneByPair($candidate, $counselor);
        if (!$conversation) {
            $conversation = new Conversation($candidate, $counselor);
            $this->entityManager->persist($conversation);
        }

        return $this->sendMessage($conversation->getId()->toRfc4122(), $candidateUser, $body);
    }

    /**
     * @return array{conversation: array<string, mixed>, message: array<string, mixed>}
     */
    public function startForStaff(User $staff, string $candidateId, string $body): array
    {
        $candidate = $this->candidateRepository->find($candidateId);
        if (!$candidate instanceof Candidate) {
            throw new NotFoundHttpException('Candidat introuvable.');
        }

        $counselor = $candidate->getAssignedCounselor();
        if (!$counselor) {
            throw new BadRequestHttpException('Aucun conseiller assigné à ce candidat.');
        }

        if ($staff->hasRole('COUNSELOR') && !$staff->hasRole('ADMIN') && $counselor->getId()->toRfc4122() !== $staff->getId()->toRfc4122()) {
            throw new AccessDeniedHttpException();
        }

        $conversation = $this->conversationRepository->findOneByPair($candidate, $counselor);
        if (!$conversation) {
            $conversation = new Conversation($candidate, $counselor);
            $this->entityManager->persist($conversation);
        }

        return $this->sendMessage($conversation->getId()->toRfc4122(), $staff, $body);
    }

    private function loadConversation(string $conversationId): Conversation
    {
        if (!Uuid::isValid($conversationId)) {
            throw new NotFoundHttpException('Conversation introuvable.');
        }

        $conversation = $this->conversationRepository->find(Uuid::fromString($conversationId));
        if (!$conversation instanceof Conversation) {
            throw new NotFoundHttpException('Conversation introuvable.');
        }

        return $conversation;
    }

    private function assertCanAccess(Conversation $conversation, User $user): void
    {
        if ($user->hasRole('SUPER_ADMIN') || $user->hasRole('ADMIN')) {
            return;
        }

        if ($user->hasRole('CANDIDATE')) {
            if (strtolower($conversation->getCandidate()->getEmail()) === strtolower($user->getEmail())) {
                return;
            }
        }

        if ($user->hasRole('COUNSELOR') && $conversation->getCounselor()->getId()->toRfc4122() === $user->getId()->toRfc4122()) {
            return;
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeConversation(Conversation $conversation, User $user): array
    {
        $candidate = $conversation->getCandidate();
        $counselor = $conversation->getCounselor();
        $lastMessage = $conversation->getMessages()->last() ?: null;

        return [
            'id' => $conversation->getId()->toRfc4122(),
            'candidate' => [
                'id' => $candidate->getId()->toRfc4122(),
                'firstName' => $candidate->getFirstName(),
                'lastName' => $candidate->getLastName(),
                'email' => $candidate->getEmail(),
            ],
            'counselor' => [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
            ],
            'unreadCount' => $this->messageRepository->countUnreadForUser($conversation, $user),
            'lastMessagePreview' => $lastMessage instanceof ConversationMessage ? mb_substr($lastMessage->getBody(), 0, 120) : null,
            'updatedAt' => $conversation->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(ConversationMessage $message): array
    {
        $author = $message->getAuthor();

        return [
            'id' => $message->getId()->toRfc4122(),
            'body' => $message->getBody(),
            'author' => [
                'id' => $author->getId()->toRfc4122(),
                'firstName' => $author->getFirstName(),
                'lastName' => $author->getLastName(),
                'email' => $author->getEmail(),
            ],
            'readAt' => $message->getReadAt()?->format(\DateTimeInterface::ATOM),
            'createdAt' => $message->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
