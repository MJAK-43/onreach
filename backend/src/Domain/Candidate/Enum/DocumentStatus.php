<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum DocumentStatus: string
{
    case MISSING = 'missing';
    case UPLOADED = 'uploaded';
    case VALIDATED = 'validated';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::MISSING => 'Manquant',
            self::UPLOADED => 'Téléversé',
            self::VALIDATED => 'Validé',
            self::REJECTED => 'Rejeté',
        };
    }
}
