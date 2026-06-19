<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PathwayStatusService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PathwayAuditLogger $auditLogger,
        private PathwayEventNotifier $eventNotifier,
    ) {
    }

    /**
     * @param array{status?: string, blockedReason?: string|null} $payload
     */
    public function updateStatus(
        Candidate $candidate,
        CandidatePathway $pathway,
        array $payload,
        User $actor,
    ): CandidatePathway {
        if ($pathway->getCandidate()->getId()->toRfc4122() !== $candidate->getId()->toRfc4122()) {
            throw new NotFoundHttpException('Parcours introuvable.');
        }

        $previousStatus = $pathway->getStatus();
        $previousReason = $pathway->getBlockedReason();

        if (isset($payload['status'])) {
            $status = PathwayInstanceStatus::tryFrom((string) $payload['status']);
            if (!$status) {
                throw new BadRequestHttpException('Statut invalide.');
            }

            if (PathwayInstanceStatus::BLOCKED === $status) {
                $reason = isset($payload['blockedReason']) ? trim((string) $payload['blockedReason']) : '';
                if ('' === $reason) {
                    throw new BadRequestHttpException('Un commentaire est obligatoire pour bloquer un parcours.');
                }
                $pathway->setBlockedReason($reason);
            } else {
                $pathway->setBlockedReason(null);
            }

            $pathway->setStatus($status);
        } elseif (\array_key_exists('blockedReason', $payload)) {
            if (PathwayInstanceStatus::BLOCKED !== $pathway->getStatus()) {
                throw new BadRequestHttpException('Le commentaire ne s\'applique qu\'à un parcours bloqué.');
            }
            $reason = trim((string) $payload['blockedReason']);
            if ('' === $reason) {
                throw new BadRequestHttpException('Le commentaire ne peut pas être vide.');
            }
            $pathway->setBlockedReason($reason);
        } else {
            throw new BadRequestHttpException('Aucune modification demandée.');
        }

        $this->auditLogger->log(
            $candidate,
            'pathway.status_updated',
            $pathway,
            null,
            [
                'previousStatus' => $previousStatus->value,
                'newStatus' => $pathway->getStatus()->value,
                'previousBlockedReason' => $previousReason,
                'newBlockedReason' => $pathway->getBlockedReason(),
            ],
            $actor,
        );

        $this->eventNotifier->notifyPathwayStatusUpdated($candidate, $pathway, $actor, $previousStatus);

        $this->entityManager->flush();

        return $pathway;
    }
}
