<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum ApplicationType: string
{
    case CAMPUS_FRANCE = 'campus_france';
    case PARCOURSUP = 'parcoursup';
    case PARIS_SACLAY = 'paris_saclay';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::CAMPUS_FRANCE => 'Campus France',
            self::PARCOURSUP => 'Parcoursup',
            self::PARIS_SACLAY => 'Paris-Saclay',
            self::AUTRE => 'Autre',
        };
    }
}
