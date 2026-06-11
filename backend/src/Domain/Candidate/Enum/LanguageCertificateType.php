<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum LanguageCertificateType: string
{
    case TCF = 'tcf';
    case DELF = 'delf';
    case DALF = 'dalf';
    case TOEFL = 'toefl';
    case IELTS = 'ielts';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::TCF => 'TCF',
            self::DELF => 'DELF',
            self::DALF => 'DALF',
            self::TOEFL => 'TOEFL',
            self::IELTS => 'IELTS',
            self::OTHER => 'Autre',
        };
    }
}
