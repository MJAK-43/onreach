<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Candidate;
use App\Entity\PathwayAuditLog;
use App\Repository\PathwayAuditLogRepository;

final readonly class PathwayAuditSerializer
{
    public function __construct(
        private PathwayAuditLogRepository $auditLogRepository,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serializeForCandidate(Candidate $candidate, int $limit = 50): array
    {
        $logs = $this->auditLogRepository->findRecentByCandidate($candidate, $limit);

        return array_map(fn (PathwayAuditLog $log) => $this->serializeEntry($log), $logs);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEntry(PathwayAuditLog $log): array
    {
        $performer = $log->getPerformedBy();
        $pathway = $log->getCandidatePathway();
        $subStep = $log->getCandidateSubStep();
        $payload = $log->getPayload() ?? [];

        return [
            'id' => $log->getId()->toRfc4122(),
            'action' => $log->getAction(),
            'actionLabel' => $this->actionLabel($log->getAction()),
            'description' => $this->buildDescription($log->getAction(), $payload, $pathway, $subStep),
            'payload' => $payload,
            'occurredAt' => $log->getOccurredAt()->format(\DateTimeInterface::ATOM),
            'performedBy' => $performer ? [
                'id' => $performer->getId()->toRfc4122(),
                'firstName' => $performer->getFirstName(),
                'lastName' => $performer->getLastName(),
                'email' => $performer->getEmail(),
            ] : null,
            'pathwayName' => $pathway?->getPathwayTemplate()->getName(),
            'subStepTitle' => $payload['subStepTitle'] ?? ($subStep ? $subStep->getSubStepTemplate()->getTitle() : null),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildDescription(
        string $action,
        array $payload,
        ?\App\Entity\CandidatePathway $pathway,
        ?\App\Entity\CandidatePathwaySubStep $subStep,
    ): string {
        $pathwayName = $pathway?->getPathwayTemplate()->getName() ?? 'Parcours';
        $subStepTitle = $payload['subStepTitle'] ?? ($subStep ? $subStep->getSubStepTemplate()->getTitle() : null);

        return match ($action) {
            'pathway.assigned' => sprintf('Parcours assigné : %s', $pathwayName),
            'substep.validation_updated' => $subStepTitle
                ? sprintf('Validation mise à jour — %s / %s', $pathwayName, $subStepTitle)
                : sprintf('Validation mise à jour — %s', $pathwayName),
            default => $this->actionLabel($action),
        };
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'pathway.assigned' => 'Parcours assigné',
            'substep.validation_updated' => 'Validation sous-étape',
            default => $action,
        };
    }
}
