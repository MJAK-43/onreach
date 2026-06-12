<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\FinancingType;
use App\Domain\Candidate\Enum\LanguageCertificateType;
use App\Entity\AcademicProfile;
use App\Entity\AcademicRecord;
use App\Entity\Candidate;
use App\Entity\FinancingProfile;
use App\Entity\Guarantor;
use App\Entity\LanguageCertificate;
use App\Entity\LanguageProfile;
use App\Entity\ProfessionalExperience;
use App\Entity\ProfessionalProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
final readonly class CandidateProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CandidateCompletionService $completionService,
        private CandidateTimelineService $timelineService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Candidate $candidate): array
    {
        $counselor = $candidate->getAssignedCounselor();
        $completion = $this->completionService->compute($candidate);

        return [
            'id' => $candidate->getId()->toRfc4122(),
            'referenceNumber' => $candidate->getReferenceNumber(),
            'createdAt' => $candidate->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $candidate->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'completion' => $completion,
            'personal' => [
                'firstName' => $candidate->getFirstName(),
                'lastName' => $candidate->getLastName(),
                'gender' => $candidate->getGender(),
                'dateOfBirth' => $candidate->getDateOfBirth()?->format('Y-m-d'),
                'placeOfBirth' => $candidate->getPlaceOfBirth(),
                'nationality' => $candidate->getNationality(),
                'maritalStatus' => $candidate->getMaritalStatus(),
                'passportNumber' => $candidate->getPassportNumber(),
                'passportIssuedAt' => $candidate->getPassportIssuedAt()?->format('Y-m-d'),
                'passportExpiresAt' => $candidate->getPassportExpiresAt()?->format('Y-m-d'),
                'passportCountry' => $candidate->getPassportCountry(),
                'identityCardNumber' => $candidate->getIdentityCardNumber(),
            ],
            'contact' => [
                'address' => $candidate->getAddress(),
                'city' => $candidate->getCity(),
                'region' => $candidate->getRegion(),
                'postalCode' => $candidate->getPostalCode(),
                'country' => $candidate->getCountry(),
                'phone' => $candidate->getPhone(),
                'whatsapp' => $candidate->getWhatsapp(),
                'email' => $candidate->getEmail(),
                'secondaryEmail' => $candidate->getSecondaryEmail(),
            ],
            'studyProject' => [
                'domain' => $candidate->getStudyDomain(),
                'specialty' => $candidate->getStudySpecialty(),
                'level' => $candidate->getStudyLevel(),
                'targetCountry' => $candidate->getStudyTargetCountry(),
                'universities' => $candidate->getStudyUniversities() ?? [],
                'description' => $candidate->getStudyDescription(),
            ],
            'careerProject' => [
                'targetJob' => $candidate->getCareerTargetJob(),
                'objectives' => $candidate->getCareerObjectives(),
                'sector' => $candidate->getCareerSector(),
                'description' => $candidate->getCareerDescription(),
            ],
            'academic' => $this->serializeAcademic($candidate),
            'languages' => $this->serializeLanguages($candidate),
            'financing' => $this->serializeFinancing($candidate),
            'experiences' => $this->serializeExperiences($candidate),
            'counselor' => $counselor instanceof User ? [
                'id' => $counselor->getId()->toRfc4122(),
                'firstName' => $counselor->getFirstName(),
                'lastName' => $counselor->getLastName(),
                'email' => $counselor->getEmail(),
                'phone' => null,
                'whatsapp' => null,
            ] : null,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(Candidate $candidate, array $payload, ?User $actor = null): Candidate
    {
        if (isset($payload['personal']) && \is_array($payload['personal'])) {
            $this->applyPersonal($candidate, $payload['personal']);
        }
        if (isset($payload['contact']) && \is_array($payload['contact'])) {
            $this->applyContact($candidate, $payload['contact']);
        }
        if (isset($payload['studyProject']) && \is_array($payload['studyProject'])) {
            $this->applyStudyProject($candidate, $payload['studyProject']);
        }
        if (isset($payload['careerProject']) && \is_array($payload['careerProject'])) {
            $this->applyCareerProject($candidate, $payload['careerProject']);
        }
        if (isset($payload['academic']) && \is_array($payload['academic'])) {
            $this->applyAcademic($candidate, $payload['academic']);
        }
        if (isset($payload['languages']) && \is_array($payload['languages'])) {
            $this->applyLanguages($candidate, $payload['languages']);
        }
        if (isset($payload['financing']) && \is_array($payload['financing'])) {
            $this->applyFinancing($candidate, $payload['financing']);
        }
        if (isset($payload['experiences']) && \is_array($payload['experiences'])) {
            $this->applyExperiences($candidate, $payload['experiences']);
        }

        $this->entityManager->flush();
        $this->timelineService->record($candidate, 'profile.updated', 'Dossier mis à jour', [], $actor);

        return $candidate;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyPersonal(Candidate $candidate, array $data): void
    {
        if (isset($data['firstName'])) {
            $candidate->setFirstName((string) $data['firstName']);
        }
        if (isset($data['lastName'])) {
            $candidate->setLastName((string) $data['lastName']);
        }
        if (\array_key_exists('gender', $data)) {
            $candidate->setGender($data['gender'] ? (string) $data['gender'] : null);
        }
        if (\array_key_exists('dateOfBirth', $data)) {
            $candidate->setDateOfBirth($this->parseDate($data['dateOfBirth']));
        }
        if (\array_key_exists('placeOfBirth', $data)) {
            $candidate->setPlaceOfBirth($data['placeOfBirth'] ? (string) $data['placeOfBirth'] : null);
        }
        if (isset($data['nationality'])) {
            $candidate->setNationality((string) $data['nationality']);
        }
        if (\array_key_exists('maritalStatus', $data)) {
            $candidate->setMaritalStatus($data['maritalStatus'] ? (string) $data['maritalStatus'] : null);
        }
        if (\array_key_exists('passportNumber', $data)) {
            $candidate->setPassportNumber($data['passportNumber'] ? (string) $data['passportNumber'] : null);
        }
        if (\array_key_exists('passportIssuedAt', $data)) {
            $candidate->setPassportIssuedAt($this->parseDate($data['passportIssuedAt']));
        }
        if (\array_key_exists('passportExpiresAt', $data)) {
            $candidate->setPassportExpiresAt($this->parseDate($data['passportExpiresAt']));
        }
        if (\array_key_exists('passportCountry', $data)) {
            $candidate->setPassportCountry($data['passportCountry'] ? (string) $data['passportCountry'] : null);
        }
        if (\array_key_exists('identityCardNumber', $data)) {
            $candidate->setIdentityCardNumber($data['identityCardNumber'] ? (string) $data['identityCardNumber'] : null);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyContact(Candidate $candidate, array $data): void
    {
        foreach (['address', 'city', 'region', 'postalCode', 'country', 'phone', 'whatsapp'] as $field) {
            if (\array_key_exists($field, $data)) {
                $setter = 'set'.ucfirst($field);
                $candidate->$setter($data[$field] ? (string) $data[$field] : null);
            }
        }
        if (\array_key_exists('secondaryEmail', $data)) {
            $candidate->setSecondaryEmail($data['secondaryEmail'] ? (string) $data['secondaryEmail'] : null);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyStudyProject(Candidate $candidate, array $data): void
    {
        if (\array_key_exists('domain', $data)) {
            $candidate->setStudyDomain($data['domain'] ? (string) $data['domain'] : null);
        }
        if (\array_key_exists('specialty', $data)) {
            $candidate->setStudySpecialty($data['specialty'] ? (string) $data['specialty'] : null);
        }
        if (\array_key_exists('level', $data)) {
            $candidate->setStudyLevel($data['level'] ? (string) $data['level'] : null);
        }
        if (\array_key_exists('targetCountry', $data)) {
            $candidate->setStudyTargetCountry($data['targetCountry'] ? (string) $data['targetCountry'] : null);
        }
        if (isset($data['universities']) && \is_array($data['universities'])) {
            $candidate->setStudyUniversities(array_values(array_map(strval(...), $data['universities'])));
        }
        if (\array_key_exists('description', $data)) {
            $candidate->setStudyDescription($data['description'] ? (string) $data['description'] : null);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyCareerProject(Candidate $candidate, array $data): void
    {
        if (\array_key_exists('targetJob', $data)) {
            $candidate->setCareerTargetJob($data['targetJob'] ? (string) $data['targetJob'] : null);
        }
        if (\array_key_exists('objectives', $data)) {
            $candidate->setCareerObjectives($data['objectives'] ? (string) $data['objectives'] : null);
        }
        if (\array_key_exists('sector', $data)) {
            $candidate->setCareerSector($data['sector'] ? (string) $data['sector'] : null);
        }
        if (\array_key_exists('description', $data)) {
            $candidate->setCareerDescription($data['description'] ? (string) $data['description'] : null);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyAcademic(Candidate $candidate, array $data): void
    {
        $profile = $candidate->getAcademicProfile();
        if (!$profile instanceof AcademicProfile) {
            $profile = new AcademicProfile();
            $profile->setCandidate($candidate);
            $candidate->setAcademicProfile($profile);
            $this->entityManager->persist($profile);
        }

        foreach (['highestDiploma' => 'highestDiploma', 'institutionName' => 'institutionName', 'specialty' => 'specialty', 'ranking' => 'ranking', 'academicAchievements' => 'academicAchievements'] as $key => $setter) {
            if (\array_key_exists($key, $data)) {
                $method = 'set'.ucfirst($setter);
                $profile->$method($data[$key] ? (string) $data[$key] : null);
            }
        }
        if (\array_key_exists('graduationYear', $data)) {
            $profile->setGraduationYear($data['graduationYear'] ? (int) $data['graduationYear'] : null);
        }
        if (\array_key_exists('overallAverage', $data)) {
            $profile->setOverallAverage($data['overallAverage'] ? (string) $data['overallAverage'] : null);
        }

        if (isset($data['records']) && \is_array($data['records'])) {
            $existing = [];
            foreach ($profile->getRecords() as $record) {
                $existing[$record->getId()->toRfc4122()] = $record;
            }
            $seen = [];
            foreach ($data['records'] as $row) {
                if (!\is_array($row)) {
                    continue;
                }
                $id = isset($row['id']) ? (string) $row['id'] : '';
                $record = isset($existing[$id]) ? $existing[$id] : new AcademicRecord(
                    (string) ($row['diploma'] ?? 'Diplôme'),
                    (string) ($row['institution'] ?? 'Établissement'),
                    (int) ($row['year'] ?? date('Y')),
                );
                if (!isset($existing[$id])) {
                    $profile->addRecord($record);
                    $this->entityManager->persist($record);
                }
                $record->setDiploma((string) ($row['diploma'] ?? $record->getDiploma()));
                $record->setInstitution((string) ($row['institution'] ?? $record->getInstitution()));
                $record->setYear((int) ($row['year'] ?? $record->getYear()));
                $record->setDiplomaType($row['diplomaType'] ?? null);
                $record->setCountry($row['country'] ?? null);
                $record->setMention($row['mention'] ?? null);
                $record->setAverage(isset($row['average']) ? (string) $row['average'] : null);
                $record->setRanking($row['ranking'] ?? null);
                $record->setAchievements($row['description'] ?? null);
                $seen[] = $record->getId()->toRfc4122();
            }
            foreach ($existing as $id => $record) {
                if (!\in_array($id, $seen, true)) {
                    $profile->removeRecord($record);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyLanguages(Candidate $candidate, array $data): void
    {
        $profile = $candidate->getLanguageProfile();
        if (!$profile instanceof LanguageProfile) {
            $profile = new LanguageProfile();
            $profile->setCandidate($candidate);
            $candidate->setLanguageProfile($profile);
            $this->entityManager->persist($profile);
        }

        if (\array_key_exists('frenchLevel', $data)) {
            $profile->setFrenchLevel($data['frenchLevel'] ? (string) $data['frenchLevel'] : null);
        }
        if (\array_key_exists('englishLevel', $data)) {
            $profile->setEnglishLevel($data['englishLevel'] ? (string) $data['englishLevel'] : null);
        }
        if (isset($data['otherLanguages']) && \is_array($data['otherLanguages'])) {
            $profile->setOtherLanguages($data['otherLanguages']);
        }

        if (isset($data['certificates']) && \is_array($data['certificates'])) {
            foreach ($profile->getCertificates()->toArray() as $cert) {
                $profile->removeCertificate($cert);
            }
            foreach ($data['certificates'] as $row) {
                if (!\is_array($row)) {
                    continue;
                }
                $type = LanguageCertificateType::tryFrom((string) ($row['type'] ?? ''));
                if (null === $type) {
                    continue;
                }
                $cert = new LanguageCertificate($type);
                $cert->setScore($row['score'] ?? null);
                $cert->setIssueDate($this->parseDate($row['issueDate'] ?? null));
                $cert->setExpirationDate($this->parseDate($row['expirationDate'] ?? null));
                $profile->addCertificate($cert);
                $this->entityManager->persist($cert);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyFinancing(Candidate $candidate, array $data): void
    {
        $profile = $candidate->getFinancingProfile();
        if (!$profile instanceof FinancingProfile) {
            $type = FinancingType::tryFrom((string) ($data['type'] ?? 'self_funded')) ?? FinancingType::SELF_FUNDED;
            $profile = new FinancingProfile($type);
            $profile->setCandidate($candidate);
            $candidate->setFinancingProfile($profile);
            $this->entityManager->persist($profile);
        }

        if (isset($data['type'])) {
            $type = FinancingType::tryFrom((string) $data['type']);
            if ($type) {
                $profile->setType($type);
            }
        }
        if (\array_key_exists('availableBudget', $data)) {
            $profile->setAvailableBudget($data['availableBudget'] ? (string) $data['availableBudget'] : null);
        }
        if (\array_key_exists('plannedAmount', $data)) {
            $profile->setPlannedAmount($data['plannedAmount'] ? (string) $data['plannedAmount'] : null);
        }
        if (\array_key_exists('description', $data)) {
            $profile->setDescription($data['description'] ? (string) $data['description'] : null);
        }

        if (isset($data['guarantors']) && \is_array($data['guarantors'])) {
            foreach ($profile->getGuarantors()->toArray() as $g) {
                $profile->removeGuarantor($g);
            }
            foreach ($data['guarantors'] as $row) {
                if (!\is_array($row)) {
                    continue;
                }
                $first = (string) ($row['firstName'] ?? '');
                $last = (string) ($row['lastName'] ?? '');
                $name = trim($first.' '.$last) ?: (string) ($row['fullName'] ?? 'Garant');
                $guarantor = new Guarantor($name);
                $guarantor->setFirstName($first ?: null);
                $guarantor->setLastName($last ?: null);
                $guarantor->setProfession($row['profession'] ?? null);
                $guarantor->setEmployer($row['employer'] ?? null);
                $guarantor->setPhone($row['phone'] ?? null);
                $guarantor->setEmail($row['email'] ?? null);
                $guarantor->setAddress($row['address'] ?? null);
                $guarantor->setMonthlyIncome(isset($row['monthlyIncome']) ? (string) $row['monthlyIncome'] : null);
                $profile->addGuarantor($guarantor);
                $this->entityManager->persist($guarantor);
            }
        }
    }

    /**
     * @param list<array<string, mixed>>|array<string, mixed> $data
     */
    private function applyExperiences(Candidate $candidate, array $data): void
    {
        if (array_is_list($data)) {
            $rows = $data;
        } elseif (isset($data['company'])) {
            $rows = [$data];
        } else {
            return;
        }

        $profile = $candidate->getProfessionalProfile();
        if (!$profile instanceof ProfessionalProfile) {
            $profile = new ProfessionalProfile();
            $profile->setCandidate($candidate);
            $candidate->setProfessionalProfile($profile);
            $this->entityManager->persist($profile);
        }

        foreach ($profile->getExperiences()->toArray() as $exp) {
            $profile->removeExperience($exp);
        }

        foreach ($rows as $row) {
            $start = $this->parseDate($row['startDate'] ?? null) ?? new \DateTimeImmutable();
            $exp = new ProfessionalExperience(
                (string) ($row['company'] ?? 'Entreprise'),
                (string) ($row['position'] ?? 'Poste'),
                $start,
            );
            $exp->setEndDate($this->parseDate($row['endDate'] ?? null));
            $exp->setDescription($row['description'] ?? null);
            $profile->addExperience($exp);
            $this->entityManager->persist($exp);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeAcademic(Candidate $candidate): ?array
    {
        $profile = $candidate->getAcademicProfile();
        if (!$profile) {
            return null;
        }

        return [
            'highestDiploma' => $profile->getHighestDiploma(),
            'institutionName' => $profile->getInstitutionName(),
            'graduationYear' => $profile->getGraduationYear(),
            'overallAverage' => $profile->getOverallAverage(),
            'ranking' => $profile->getRanking(),
            'specialty' => $profile->getSpecialty(),
            'academicAchievements' => $profile->getAcademicAchievements(),
            'records' => array_map(static fn (AcademicRecord $r) => [
                'id' => $r->getId()->toRfc4122(),
                'diplomaType' => $r->getDiplomaType(),
                'diploma' => $r->getDiploma(),
                'institution' => $r->getInstitution(),
                'country' => $r->getCountry(),
                'year' => $r->getYear(),
                'mention' => $r->getMention(),
                'average' => $r->getAverage(),
                'ranking' => $r->getRanking(),
                'description' => $r->getAchievements(),
            ], $profile->getRecords()->toArray()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeLanguages(Candidate $candidate): ?array
    {
        $profile = $candidate->getLanguageProfile();
        if (!$profile) {
            return null;
        }

        return [
            'frenchLevel' => $profile->getFrenchLevel(),
            'englishLevel' => $profile->getEnglishLevel(),
            'otherLanguages' => $profile->getOtherLanguages() ?? [],
            'certificates' => array_map(static fn (LanguageCertificate $c) => [
                'id' => $c->getId()->toRfc4122(),
                'type' => $c->getType()->value,
                'score' => $c->getScore(),
                'issueDate' => $c->getIssueDate()?->format('Y-m-d'),
                'expirationDate' => $c->getExpirationDate()?->format('Y-m-d'),
            ], $profile->getCertificates()->toArray()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeFinancing(Candidate $candidate): ?array
    {
        $profile = $candidate->getFinancingProfile();
        if (!$profile) {
            return null;
        }

        return [
            'type' => $profile->getType()->value,
            'availableBudget' => $profile->getAvailableBudget(),
            'plannedAmount' => $profile->getPlannedAmount(),
            'description' => $profile->getDescription(),
            'guarantors' => array_map(static fn (Guarantor $g) => [
                'id' => $g->getId()->toRfc4122(),
                'firstName' => $g->getFirstName(),
                'lastName' => $g->getLastName(),
                'fullName' => $g->getFullName(),
                'profession' => $g->getProfession(),
                'employer' => $g->getEmployer(),
                'phone' => $g->getPhone(),
                'email' => $g->getEmail(),
                'address' => $g->getAddress(),
                'monthlyIncome' => $g->getMonthlyIncome(),
            ], $profile->getGuarantors()->toArray()),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serializeExperiences(Candidate $candidate): array
    {
        $profile = $candidate->getProfessionalProfile();
        if (!$profile) {
            return [];
        }

        return array_map(static fn (ProfessionalExperience $e) => [
            'id' => $e->getId()->toRfc4122(),
            'company' => $e->getCompany(),
            'position' => $e->getPosition(),
            'startDate' => $e->getStartDate()->format('Y-m-d'),
            'endDate' => $e->getEndDate()?->format('Y-m-d'),
            'description' => $e->getDescription(),
        ], $profile->getExperiences()->toArray());
    }

    private function parseDate(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Exception) {
            throw new BadRequestHttpException('Date invalide : '.$value);
        }
    }
}
