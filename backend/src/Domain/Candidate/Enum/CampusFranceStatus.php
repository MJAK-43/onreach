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
    case VISA_PENDING = 'visa_pending';
    case VISA_OBTAINED = 'visa_obtained';
    case COMPLETED = 'completed';
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
            self::VISA_PENDING => 'Visa en attente',
            self::VISA_OBTAINED => 'Visa obtenu',
            self::COMPLETED => 'Terminé',
            self::REJECTED => 'Refusé',
        };
    }

    /** @return list<self> */
    public static function workflowOrder(): array
    {
        return [
            self::DRAFT,
            self::PENDING_DOCUMENTS,
            self::SUBMITTED,
            self::INTERVIEW_SCHEDULED,
            self::INTERVIEW_PASSED,
            self::ADMISSION_OBTAINED,
            self::VISA_PENDING,
            self::VISA_OBTAINED,
            self::COMPLETED,
        ];
    }
}
