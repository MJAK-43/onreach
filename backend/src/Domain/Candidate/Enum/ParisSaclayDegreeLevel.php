<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum ParisSaclayDegreeLevel: string
{
    case LICENCE = 'licence';
    case MASTER = 'master';
    case DOCTORATE = 'doctorate';

    public function label(): string
    {
        return match ($this) {
            self::LICENCE => 'Licence',
            self::MASTER => 'Master',
            self::DOCTORATE => 'Doctorat',
        };
    }
}
