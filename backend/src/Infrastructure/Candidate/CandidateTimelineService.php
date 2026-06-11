<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Entity\Candidate;
use App\Entity\CandidateTimelineEntry;
use App\Entity\User;
use App\Repository\CandidateTimelineEntryRepository;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class CandidateTimelineService
{
    public function __construct(
        private CandidateTimelineEntryRepository $repository,
        private Security $security,
    ) {
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function record(Candidate $candidate, string $action, string $description, ?array $metadata = null, ?User $actor = null): void
    {
        $user = $actor ?? $this->security->getUser();
        $entry = new CandidateTimelineEntry($candidate, $action, $description, $metadata);
        if ($user instanceof User) {
            $entry->setActor($user);
        }
        $this->repository->save($entry);
    }
}
