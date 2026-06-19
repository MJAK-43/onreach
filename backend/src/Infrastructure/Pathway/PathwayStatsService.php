<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Entity\User;
use App\Repository\CandidatePathwayRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class PathwayStatsService
{
    public function __construct(
        private CandidatePathwayRepository $pathwayRepository,
        private Security $security,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function aggregate(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $scopeCounselor = null;
        if ($user->hasRole('COUNSELOR') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER_ADMIN')) {
            $scopeCounselor = $user;
        }

        $pathways = $this->pathwayRepository->findForTracking(null, null, null, null, null, $scopeCounselor);

        $byStatus = [];
        $byPathway = [];
        $blocked = 0;
        $overdue = 0;
        $today = new \DateTimeImmutable('today');

        foreach ($pathways as $pathway) {
            $status = $pathway->getStatus()->value;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $code = $pathway->getPathwayTemplate()->getCode()->value;
            $byPathway[$code] = ($byPathway[$code] ?? 0) + 1;

            if (PathwayInstanceStatus::BLOCKED === $pathway->getStatus()) {
                ++$blocked;
            }

            $hasOverdue = false;
            foreach ($pathway->getStages() as $stage) {
                foreach ($stage->getSubSteps() as $subStep) {
                    $due = $subStep->getDueDate();
                    if ($due && $due < $today && !$subStep->isValidated(false)) {
                        $hasOverdue = true;
                        break 2;
                    }
                }
            }
            if ($hasOverdue) {
                ++$overdue;
            }
        }

        $pathwayLabels = [];
        foreach (PathwayCode::cases() as $case) {
            $pathwayLabels[$case->value] = $case->label();
        }

        $statusLabels = [];
        foreach (PathwayInstanceStatus::cases() as $case) {
            $statusLabels[$case->value] = $case->label();
        }

        $byStatusItems = [];
        foreach ($byStatus as $code => $count) {
            $byStatusItems[] = [
                'code' => $code,
                'label' => $statusLabels[$code] ?? $code,
                'count' => $count,
            ];
        }

        $byPathwayItems = [];
        foreach ($byPathway as $code => $count) {
            $byPathwayItems[] = [
                'code' => $code,
                'label' => $pathwayLabels[$code] ?? $code,
                'count' => $count,
            ];
        }

        return [
            'totalPathways' => \count($pathways),
            'blockedCount' => $blocked,
            'overdueCount' => $overdue,
            'byStatus' => $byStatusItems,
            'byPathway' => $byPathwayItems,
        ];
    }
}
