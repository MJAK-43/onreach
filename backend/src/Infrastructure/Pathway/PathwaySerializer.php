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
        $settings = $this->settingRepository->findAllIndexedByCode();

        return array_map(
            fn (CandidatePathway $pathway) => $this->serializePathway($pathway, $settings),
            $pathways,
        );
    }

    /**
     * @param array<string, \App\Entity\PathwaySetting> $settings
     *
     * @return array<string, mixed>
     */
    public function serializePathway(CandidatePathway $pathway, ?array $settings = null): array
    {
        $template = $pathway->getPathwayTemplate();
        $settings ??= $this->settingRepository->findAllIndexedByCode();
        $code = $template->getCode()->value;
        $context = PathwayValidationContext::fromSetting($settings[$code] ?? null);

        return [
            'id' => $pathway->getId()->toRfc4122(),
            'code' => $template->getCode()->value,
            'name' => $template->getName(),
            'status' => $pathway->getStatus()->value,
            'statusLabel' => $pathway->getStatus()->label(),
            'progressPercent' => $pathway->getProgressPercent(),
            'blockedReason' => $pathway->getBlockedReason(),
            'doubleValidationEnabled' => $context->doubleValidationEnabled,
            'updatedAt' => $pathway->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'stages' => array_map(
                fn (CandidatePathwayStage $stage) => $this->serializeStage($stage, $context),
                $pathway->getStages()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeStage(CandidatePathwayStage $stage, PathwayValidationContext $context): array
    {
        $template = $stage->getStageTemplate();

        return [
            'id' => $stage->getId()->toRfc4122(),
            'title' => $template->getTitle(),
            'description' => $template->getDescription(),
            'sortOrder' => $stage->getSortOrder(),
            'progressPercent' => $stage->getProgressPercent(),
            'subSteps' => array_map(
                fn (CandidatePathwaySubStep $subStep) => $this->serializeSubStep($subStep, $context),
                $stage->getSubSteps()->toArray(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSubStep(CandidatePathwaySubStep $subStep, PathwayValidationContext $context): array
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
            'validated' => $context->isSubStepValidated($subStep),
            'pendingAdminValidation' => $context->isPendingAdminValidation($subStep),
            'grandfatheredValidation' => $subStep->isGrandfatheredValidation(),
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
