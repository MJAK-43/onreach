<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\PathwayDueDateReminderMessage;
use App\Entity\CandidatePathwaySubStep;
use App\Infrastructure\Pathway\PathwayEventNotifier;
use App\Repository\CandidatePathwaySubStepRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class PathwayDueDateReminderHandler
{
    public function __construct(
        private CandidatePathwaySubStepRepository $subStepRepository,
        private PathwayEventNotifier $eventNotifier,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(PathwayDueDateReminderMessage $message): void
    {
        if (!Uuid::isValid($message->subStepId)) {
            return;
        }

        $subStep = $this->subStepRepository->find(Uuid::fromString($message->subStepId));
        if (!$subStep instanceof CandidatePathwaySubStep) {
            return;
        }

        if (null !== $subStep->getDueReminderSentAt()) {
            return;
        }

        if ($subStep->isValidated(false)) {
            return;
        }

        $this->eventNotifier->notifyDueDateReminder($subStep);
        $subStep->markDueReminderSent();
        $this->entityManager->flush();
    }
}
