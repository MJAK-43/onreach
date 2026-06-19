<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\PathwayAuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PathwayAuditLogger
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function log(
        Candidate $candidate,
        string $action,
        ?CandidatePathway $pathway = null,
        ?CandidatePathwaySubStep $subStep = null,
        ?array $payload = null,
        ?User $performedBy = null,
    ): void {
        $this->entityManager->persist(new PathwayAuditLog(
            $candidate,
            $action,
            $pathway,
            $subStep,
            $payload,
            $performedBy,
        ));
    }
}
