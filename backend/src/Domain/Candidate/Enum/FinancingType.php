<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum FinancingType: string
{
    case SELF_FUNDED = 'self_funded';
    case PARENT = 'parent';
    case SPONSOR = 'sponsor';
    case SCHOLARSHIP = 'scholarship';
    case COMPANY = 'company';

    public function label(): string
    {
        return match ($this) {
            self::SELF_FUNDED => 'Autofinancement',
            self::PARENT => 'Parents',
            self::SPONSOR => 'Sponsor',
            self::SCHOLARSHIP => 'Bourse',
            self::COMPANY => 'Entreprise',
        };
    }
}
