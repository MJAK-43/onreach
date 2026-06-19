<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Entity\Candidate;
use App\Entity\User;

final readonly class CandidateDashboardService
{
    public function __construct(
        private ChecklistService $checklistService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Candidate $candidate): array
    {
        $counselor = $candidate->getAssignedCounselor();
        $checklist = $this->checklistService->getProgressForCandidate($candidate, ApplicationType::CAMPUS_FRANCE);

        return [
            'id' => $candidate->getId()->toRfc4122(),
            'status' => $candidate->getStatus()->value,
            'statusLabel' => $candidate->getStatus()->label(),
            'counselor' => $counselor instanceof User ? [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
            ] : null,
            'checklist' => $checklist,
            'documents' => array_map(static fn ($doc) => [
                'type' => $doc->getType()->value,
                'status' => $doc->getStatus()->value,
            ], $candidate->getDocuments()->toArray()),
        ];
    }
}
