<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwayStage;
use App\Entity\CandidatePathwaySubStep;
use App\Repository\PathwaySettingRepository;

final readonly class PathwayProgressCalculator
{
    public function __construct(
        private PathwaySettingRepository $settingRepository,
    ) {
    }

    public function refresh(CandidatePathway $pathway): void
    {
        $doubleValidation = $this->isDoubleValidationEnabled($pathway);

        foreach ($pathway->getStages() as $stage) {
            $stage->setProgressPercent($this->computeStageProgress($stage, $doubleValidation));
        }

        $pathway->setProgressPercent($this->computePathwayProgress($pathway, $doubleValidation));
        $this->refreshPathwayStatus($pathway, $doubleValidation);
    }

    private function isDoubleValidationEnabled(CandidatePathway $pathway): bool
    {
        $setting = $this->settingRepository->findOneByPathwayCode(
            $pathway->getPathwayTemplate()->getCode(),
        );

        return $setting?->isDoubleValidationEnabled() ?? false;
    }

    private function computeStageProgress(CandidatePathwayStage $stage, bool $doubleValidation): int
    {
        $required = 0;
        $validated = 0;

        foreach ($stage->getSubSteps() as $subStep) {
            if (!$subStep->getSubStepTemplate()->isRequired()) {
                continue;
            }
            ++$required;
            if ($subStep->isValidated($doubleValidation)) {
                ++$validated;
            }
        }

        if (0 === $required) {
            return 100;
        }

        return (int) round(($validated / $required) * 100);
    }

    private function computePathwayProgress(CandidatePathway $pathway, bool $doubleValidation): int
    {
        $stages = $pathway->getStages()->toArray();
        if ([] === $stages) {
            return 0;
        }

        $total = 0;
        foreach ($stages as $stage) {
            $total += $this->computeStageProgress($stage, $doubleValidation);
        }

        return (int) round($total / \count($stages));
    }

    private function refreshPathwayStatus(CandidatePathway $pathway, bool $doubleValidation): void
    {
        if (\App\Domain\Pathway\Enum\PathwayInstanceStatus::BLOCKED === $pathway->getStatus()) {
            return;
        }

        $progress = $pathway->getProgressPercent();
        if (0 === $progress) {
            $pathway->setStatus(\App\Domain\Pathway\Enum\PathwayInstanceStatus::NOT_STARTED);

            return;
        }

        if (100 === $progress) {
            $pathway->setStatus(\App\Domain\Pathway\Enum\PathwayInstanceStatus::ACCEPTED);

            return;
        }

        $pathway->setStatus(\App\Domain\Pathway\Enum\PathwayInstanceStatus::IN_PROGRESS);
    }

    public function isSubStepValidated(CandidatePathwaySubStep $subStep, bool $doubleValidation): bool
    {
        return $subStep->isValidated($doubleValidation);
    }
}
