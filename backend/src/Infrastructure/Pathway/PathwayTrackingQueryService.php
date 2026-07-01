<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\User;
use App\Repository\CandidatePathwayRepository;
use App\Repository\PathwaySettingRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class PathwayTrackingQueryService
{
    public function __construct(
        private CandidatePathwayRepository $pathwayRepository,
        private PathwaySettingRepository $settingRepository,
        private Security $security,
    ) {
    }

    /**
     * @param array{
     *     pathway?: string|null,
     *     status?: string|null,
     *     campaign?: int|null,
     *     counselor?: string|null,
     *     search?: string|null
     * } $filters
     *
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public function list(array $filters): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $scopeCounselor = null;
        if ($user->hasRole('COUNSELOR') && !$user->hasRole('ADMIN') && !$user->hasRole('SUPER_ADMIN')) {
            $scopeCounselor = $user;
        }

        $counselorId = isset($filters['counselor']) && Uuid::isValid((string) $filters['counselor'])
            ? Uuid::fromString((string) $filters['counselor'])
            : null;

        $pathwayCode = isset($filters['pathway']) ? PathwayCode::tryFrom((string) $filters['pathway']) : null;
        $status = isset($filters['status']) ? PathwayInstanceStatus::tryFrom((string) $filters['status']) : null;
        $campaignYear = isset($filters['campaign']) ? (int) $filters['campaign'] : null;
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;

        $pathways = $this->pathwayRepository->findForTracking(
            $pathwayCode,
            $status,
            $campaignYear > 0 ? $campaignYear : null,
            $counselorId,
            '' !== $search ? $search : null,
            $scopeCounselor,
        );

        $items = array_map(fn (CandidatePathway $pathway) => $this->serializeRow($pathway), $pathways);

        return [
            'items' => $items,
            'total' => \count($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeRow(CandidatePathway $pathway): array
    {
        $candidate = $pathway->getCandidate();
        $template = $pathway->getPathwayTemplate();
        $campaign = $template->getCampaign();
        $counselor = $candidate->getAssignedCounselor();
        $context = PathwayValidationContext::fromSetting(
            $this->settingRepository->findOneByPathwayCode($template->getCode()),
        );

        $nextSubStep = $this->findNextPendingSubStep($pathway, $context);

        return [
            'pathwayId' => $pathway->getId()->toRfc4122(),
            'candidateId' => $candidate->getId()->toRfc4122(),
            'candidateFirstName' => $candidate->getFirstName(),
            'candidateLastName' => $candidate->getLastName(),
            'candidateEmail' => $candidate->getEmail(),
            'pathwayCode' => $template->getCode()->value,
            'pathwayName' => $template->getName(),
            'status' => $pathway->getStatus()->value,
            'statusLabel' => $pathway->getStatus()->label(),
            'progressPercent' => $pathway->getProgressPercent(),
            'blockedReason' => $pathway->getBlockedReason(),
            'campaignYear' => $campaign->getYear(),
            'campaignName' => $campaign->getName(),
            'counselor' => $counselor ? [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
            ] : null,
            'nextSubStepTitle' => $nextSubStep?->getSubStepTemplate()->getTitle(),
            'nextDueDate' => $nextSubStep?->getDueDate()?->format('Y-m-d'),
            'updatedAt' => $pathway->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function findNextPendingSubStep(CandidatePathway $pathway, PathwayValidationContext $context): ?CandidatePathwaySubStep
    {
        $pending = [];

        foreach ($pathway->getStages() as $stage) {
            foreach ($stage->getSubSteps() as $subStep) {
                if (!$context->isSubStepValidated($subStep)) {
                    $pending[] = $subStep;
                }
            }
        }

        if ([] === $pending) {
            return null;
        }

        usort($pending, static function (CandidatePathwaySubStep $a, CandidatePathwaySubStep $b): int {
            $dueA = $a->getDueDate();
            $dueB = $b->getDueDate();
            if ($dueA && $dueB) {
                return $dueA <=> $dueB;
            }
            if ($dueA) {
                return -1;
            }
            if ($dueB) {
                return 1;
            }

            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $pending[0];
    }
}
