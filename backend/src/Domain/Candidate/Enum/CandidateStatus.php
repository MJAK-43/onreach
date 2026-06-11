<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum CandidateStatus: string
{
    case LEAD = 'lead';
    case PROFILE_INCOMPLETE = 'profile_incomplete';
    case DOCUMENTS_PENDING = 'documents_pending';
    case IN_PROGRESS = 'in_progress';
    case ADMISSION_OBTAINED = 'admission_obtained';
    case VISA_OBTAINED = 'visa_obtained';
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::LEAD => 'Prospect',
            self::PROFILE_INCOMPLETE => 'Profil incomplet',
            self::DOCUMENTS_PENDING => 'Documents en attente',
            self::IN_PROGRESS => 'En cours',
            self::ADMISSION_OBTAINED => 'Admission obtenue',
            self::VISA_OBTAINED => 'Visa obtenu',
            self::COMPLETED => 'Clôturé',
            self::SUSPENDED => 'Suspendu',
            self::CANCELLED => 'Annulé',
        };
    }
}
