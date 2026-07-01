<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayCode;
use App\Entity\PathwaySetting;
use App\Repository\CandidatePathwaySubStepRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PathwayDoubleValidationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandidatePathwaySubStepRepository $subStepRepository,
        private PathwayProgressCalculator $progressCalculator,
    ) {
    }

    public function applySettingChange(PathwaySetting $setting, bool $wasEnabled, bool $isEnabled): void
    {
        if (!$wasEnabled && $isEnabled) {
            $this->grandfatherExistingCounselorValidations($setting->getPathwayCode());
        }

        if ($wasEnabled && !$isEnabled) {
            $this->clearGrandfatheredFlags($setting->getPathwayCode());
        }

        $this->refreshProgressForPathwayCode($setting->getPathwayCode());
        $this->entityManager->flush();
    }

    private function grandfatherExistingCounselorValidations(PathwayCode $code): void
    {
        foreach ($this->subStepRepository->findByPathwayCode($code) as $subStep) {
            if (null !== $subStep->getCounselorValidatedAt() && null === $subStep->getAdminValidatedAt()) {
                $subStep->setGrandfatheredValidation(true);
            }
        }
    }

    private function clearGrandfatheredFlags(PathwayCode $code): void
    {
        foreach ($this->subStepRepository->findByPathwayCode($code) as $subStep) {
            $subStep->setGrandfatheredValidation(false);
        }
    }

    private function refreshProgressForPathwayCode(PathwayCode $code): void
    {
        foreach ($this->subStepRepository->findPathwaysByCode($code) as $pathway) {
            $this->progressCalculator->refresh($pathway);
        }
    }
}
