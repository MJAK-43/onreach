<?php

declare(strict_types=1);

namespace App\Domain\Pathway\Enum;

enum StudyApplicationType: string
{
    case FIRST_YEAR = 'first_year';
    case CONTINUING = 'continuing';

    public function label(): string
    {
        return match ($this) {
            self::FIRST_YEAR => "Première année d'études en France",
            self::CONTINUING => "Poursuite d'études en France",
        };
    }
}
