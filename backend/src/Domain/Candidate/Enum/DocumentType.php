<?php

declare(strict_types=1);

namespace App\Domain\Candidate\Enum;

enum DocumentType: string
{
    case PASSPORT = 'passport';
    case IDENTITY_CARD = 'identity_card';
    case PHOTO = 'photo';
    case CV = 'cv';
    case MOTIVATION_LETTER = 'motivation_letter';
    case DIPLOMA = 'diploma';
    case TRANSCRIPT = 'transcript';
    case LANGUAGE_CERTIFICATE = 'language_certificate';
    case TCF = 'tcf';
    case DELF = 'delf';
    case DALF = 'dalf';
    case TOEFL = 'toefl';
    case IELTS = 'ielts';
    case SUPPORT_ATTESTATION = 'support_attestation';
    case RECOMMENDATION_LETTER = 'recommendation_letter';
    case RESEARCH_PROJECT = 'research_project';
    case INTERNSHIP_REPORT = 'internship_report';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PASSPORT => 'Passeport',
            self::IDENTITY_CARD => 'Carte d\'identité',
            self::PHOTO => 'Photo',
            self::CV => 'CV',
            self::MOTIVATION_LETTER => 'Lettre de motivation',
            self::DIPLOMA => 'Diplôme',
            self::TRANSCRIPT => 'Relevé de notes',
            self::LANGUAGE_CERTIFICATE => 'Certificat de langue',
            self::TCF => 'TCF',
            self::DELF => 'DELF',
            self::DALF => 'DALF',
            self::TOEFL => 'TOEFL',
            self::IELTS => 'IELTS',
            self::SUPPORT_ATTESTATION => 'Attestation de prise en charge',
            self::RECOMMENDATION_LETTER => 'Lettre de recommandation',
            self::RESEARCH_PROJECT => 'Projet de recherche',
            self::INTERNSHIP_REPORT => 'Rapport de stage',
            self::OTHER => 'Autre',
        };
    }
}
