<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwayStage;
use App\Entity\CandidatePathwaySubStep;
use App\Repository\PathwaySettingRepository;

final readonly class PathwaySerializer
{
    public function __construct(
        private PathwaySettingRepository $settingRepository,
    ) {
    }

    /**
     * @param list<CandidatePathway> $pathways
     *
     * @return list<array<string, mixed>>
     */
    public function serializeMany(array $pathways): array
    {
        return array_map(fn (CandidatePathway $p) => $this->serializePathway($p), $pathways);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializePathway(CandidatePathway $pathway): array
    {
        $template = $pathway->getPathwayTemplate();
        $doubleValidation = $this->settingRepository
            ->findOneByPathwayCode($template->getCode())
            ?->isDoubleValidationEnabled() ?? false;

        return [
            'id' => $pathway->getId()->toRfc4122(),
            'code' => $template->getCode()->value,
            'name' => $template->getName(),
            'status' => $pathway->getStatus()->value,
            'statusLabel' => $pathway->getStatus()->label(),
            'progressPercent' => $pathway->getProgressPercent(),
            'blockedReason' => $pathway->getBlockedReason(),
            'doubleValidationEnabled' => $doubleValidation,
            'updatedAt' => $pathway->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'stages' => array_map(
                fn (CandidatePathwayStage $stage) => $this->serializeStage($stage, $doubleValidation),
                $pathway->getStages()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeStage(CandidatePathwayStage $stage, bool $doubleValidation): array
    {
        $template = $stage->getStageTemplate();

        return [
            'id' => $stage->getId()->toRfc4122(),
            'title' => $template->getTitle(),
            'description' => $template->getDescription(),
            'sortOrder' => $stage->getSortOrder(),
            'progressPercent' => $stage->getProgressPercent(),
            'subSteps' => array_map(
                fn (CandidatePathwaySubStep $subStep) => $this->serializeSubStep($subStep, $doubleValidation),
                $stage->getSubSteps()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSubStep(CandidatePathwaySubStep $subStep, bool $doubleValidation): array
    {
        $template = $subStep->getSubStepTemplate();
        $counselor = $subStep->getCounselorValidatedBy();
        $admin = $subStep->getAdminValidatedBy();

        return [
            'id' => $subStep->getId()->toRfc4122(),
            'title' => $template->getTitle(),
            'description' => $template->getDescription(),
            'required' => $template->isRequired(),
            'sortOrder' => $subStep->getSortOrder(),
            'dueDate' => $subStep->getDueDate()?->format('Y-m-d'),
            'validated' => $subStep->isValidated($doubleValidation),
            'counselorValidatedAt' => $subStep->getCounselorValidatedAt()?->format(\DateTimeInterface::ATOM),
            'counselorValidatedBy' => $counselor ? $this->serializeUser($counselor) : null,
            'adminValidatedAt' => $subStep->getAdminValidatedAt()?->format(\DateTimeInterface::ATOM),
            'adminValidatedBy' => $admin ? $this->serializeUser($admin) : null,
            'updatedAt' => $subStep->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array{id: string, firstName: string, lastName: string, email: string}
     */
    private function serializeUser(\App\Entity\User $user): array
    {
        return [
            'id' => $user->getId()->toRfc4122(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ];
    }
}
