<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\CandidatePathwaySubStep;
use App\Entity\PathwaySetting;

final readonly class PathwayValidationContext
{
    public function __construct(
        public bool $doubleValidationEnabled,
        public ?\DateTimeImmutable $doubleValidationEnabledAt,
    ) {
    }

    public static function fromSetting(?PathwaySetting $setting): self
    {
        return new self(
            $setting?->isDoubleValidationEnabled() ?? false,
            $setting?->getDoubleValidationEnabledAt(),
        );
    }

    public function isSubStepValidated(CandidatePathwaySubStep $subStep): bool
    {
        return $subStep->isValidated($this->doubleValidationEnabled, $this->doubleValidationEnabledAt);
    }

    public function isPendingAdminValidation(CandidatePathwaySubStep $subStep): bool
    {
        return $subStep->isPendingAdminValidation($this->doubleValidationEnabled, $this->doubleValidationEnabledAt);
    }
}
