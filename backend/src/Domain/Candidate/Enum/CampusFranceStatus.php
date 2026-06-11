<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum CampusFranceStatus: string
{
    case DRAFT = 'draft';
    case PENDING_DOCUMENTS = 'pending_documents';
    case SUBMITTED = 'submitted';
    case INTERVIEW_SCHEDULED = 'interview_scheduled';
    case INTERVIEW_PASSED = 'interview_passed';
    case ADMISSION_OBTAINED = 'admission_obtained';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING_DOCUMENTS => 'Documents en attente',
            self::SUBMITTED => 'Déposé',
            self::INTERVIEW_SCHEDULED => 'Entretien programmé',
            self::INTERVIEW_PASSED => 'Entretien réussi',
            self::ADMISSION_OBTAINED => 'Admission obtenue',
            self::REJECTED => 'Refusé',
        };
    }
}
