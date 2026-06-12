<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Entity\Candidate;

final readonly class CandidateCompletionService
{
    /** @var list<DocumentType> */
    private const REQUIRED_DOCUMENT_TYPES = [
        DocumentType::PASSPORT,
        DocumentType::PHOTO,
        DocumentType::CV,
        DocumentType::MOTIVATION_LETTER,
        DocumentType::DIPLOMA,
        DocumentType::TRANSCRIPT,
    ];

    /** @var list<DocumentType> */
    private const LANGUAGE_DOCUMENT_TYPES = [
        DocumentType::LANGUAGE_CERTIFICATE,
        DocumentType::TCF,
        DocumentType::DELF,
        DocumentType::DALF,
        DocumentType::TOEFL,
        DocumentType::IELTS,
    ];

    /**
     * @return array{profile: int, documents: int, academic: int, financing: int, global: int}
     */
    public function compute(Candidate $candidate): array
    {
        $profile = $this->percentProfile($candidate);
        $documents = $this->percentDocuments($candidate);
        $academic = $this->percentAcademic($candidate);
        $financing = $this->percentFinancing($candidate);
        $global = (int) round(($profile + $documents + $academic + $financing) / 4);

        return [
            'profile' => $profile,
            'documents' => $documents,
            'academic' => $academic,
            'financing' => $financing,
            'global' => $global,
        ];
    }

    private function percentProfile(Candidate $candidate): int
    {
        $fields = [
            $candidate->getFirstName(),
            $candidate->getLastName(),
            $candidate->getGender(),
            $candidate->getDateOfBirth()?->format('Y-m-d'),
            $candidate->getPlaceOfBirth(),
            $candidate->getNationality(),
            $candidate->getMaritalStatus(),
            $candidate->getPassportNumber(),
            $candidate->getPhone(),
            $candidate->getEmail(),
            $candidate->getAddress(),
            $candidate->getCity(),
            $candidate->getCountry(),
        ];

        return $this->ratio($fields);
    }

    private function percentDocuments(Candidate $candidate): int
    {
        $requiredSlots = count(self::REQUIRED_DOCUMENT_TYPES) + 1;
        $uploaded = 0;

        foreach (self::REQUIRED_DOCUMENT_TYPES as $type) {
            if ($this->hasUploadedDocument($candidate, $type)) {
                ++$uploaded;
            }
        }

        if ($this->hasLanguageCertificate($candidate)) {
            ++$uploaded;
        }

        return (int) round(($uploaded / $requiredSlots) * 100);
    }

    private function hasUploadedDocument(Candidate $candidate, DocumentType $type): bool
    {
        foreach ($candidate->getDocuments() as $document) {
            if ($document->getType() === $type && DocumentStatus::MISSING !== $document->getStatus()) {
                return true;
            }
        }

        return false;
    }

    private function hasLanguageCertificate(Candidate $candidate): bool
    {
        foreach (self::LANGUAGE_DOCUMENT_TYPES as $type) {
            if ($this->hasUploadedDocument($candidate, $type)) {
                return true;
            }
        }

        return false;
    }

    private function percentAcademic(Candidate $candidate): int
    {
        $profile = $candidate->getAcademicProfile();
        if (null === $profile) {
            return 0;
        }

        $fields = [
            $profile->getHighestDiploma(),
            $profile->getInstitutionName(),
            $profile->getGraduationYear(),
        ];
        $base = $this->ratio($fields);
        $hasRecord = $profile->getRecords()->count() > 0 ? 30 : 0;

        return min(100, $base + $hasRecord);
    }

    private function percentFinancing(Candidate $candidate): int
    {
        $profile = $candidate->getFinancingProfile();
        if (null === $profile) {
            return 0;
        }

        $fields = [
            $profile->getType()->value,
            $profile->getAvailableBudget(),
            $profile->getPlannedAmount(),
        ];
        $base = $this->ratio($fields);
        $hasGuarantor = $profile->getGuarantors()->count() > 0 ? 25 : 0;

        return min(100, $base + $hasGuarantor);
    }

    /**
     * @param list<mixed> $fields
     */
    private function ratio(array $fields): int
    {
        if ([] === $fields) {
            return 0;
        }

        $filled = count(array_filter($fields, static fn ($v) => null !== $v && '' !== $v));

        return (int) round(($filled / count($fields)) * 100);
    }
}
