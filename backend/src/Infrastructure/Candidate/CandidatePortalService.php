<?php

declare(strict_types=1);

namespace App\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Domain\Candidate\Enum\CampusFranceStatus;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Domain\Candidate\Enum\ParcoursupWishStatus;
use App\Domain\Candidate\Enum\ParisSaclayStatus;
use App\Entity\CampusFranceApplication;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Entity\CandidateTimelineEntry;
use App\Entity\ParcoursupApplication;
use App\Entity\ParcoursupWish;
use App\Entity\User;

final readonly class CandidatePortalService
{
    /** @var list<array{key: string, label: string}> */
    private const CAMPUS_FRANCE_WORKFLOW = [
        ['key' => 'dossier_created', 'label' => 'Dossier créé'],
        ['key' => 'documents_received', 'label' => 'Documents reçus'],
        ['key' => 'documents_validated', 'label' => 'Documents validés'],
        ['key' => 'submitted', 'label' => 'Campus France soumis'],
        ['key' => 'interview_scheduled', 'label' => 'Entretien planifié'],
        ['key' => 'interview_done', 'label' => 'Entretien réalisé'],
        ['key' => 'admission_obtained', 'label' => 'Admission obtenue'],
        ['key' => 'visa_pending', 'label' => 'Demande Visa'],
        ['key' => 'visa_obtained', 'label' => 'Visa obtenu'],
        ['key' => 'departure', 'label' => 'Départ'],
    ];

    /** @var list<array{key: string, label: string}> */
    private const PARIS_SACLAY_WORKFLOW = [
        ['key' => 'dossier_created', 'label' => 'Dossier créé'],
        ['key' => 'documents_received', 'label' => 'Documents reçus'],
        ['key' => 'documents_validated', 'label' => 'Documents validés'],
        ['key' => 'submitted', 'label' => 'Candidature soumise'],
        ['key' => 'under_review', 'label' => 'Étude dossier'],
        ['key' => 'admission_obtained', 'label' => 'Admission obtenue'],
        ['key' => 'visa', 'label' => 'Visa'],
        ['key' => 'departure', 'label' => 'Départ'],
    ];

    /** @var list<DocumentType> */
    private const CAMPUS_FRANCE_DOCUMENT_TYPES = [
        DocumentType::PASSPORT,
        DocumentType::PHOTO,
        DocumentType::CV,
        DocumentType::MOTIVATION_LETTER,
        DocumentType::DIPLOMA,
        DocumentType::TRANSCRIPT,
        DocumentType::LANGUAGE_CERTIFICATE,
        DocumentType::OTHER,
    ];

    /** @var list<DocumentType> */
    private const PARCOURSUP_DOCUMENT_TYPES = [
        DocumentType::TRANSCRIPT,
        DocumentType::DIPLOMA,
        DocumentType::MOTIVATION_LETTER,
        DocumentType::OTHER,
        DocumentType::CV,
        DocumentType::OTHER,
    ];

    /** @var list<DocumentType> */
    private const PARIS_SACLAY_DOCUMENT_TYPES = [
        DocumentType::PASSPORT,
        DocumentType::CV,
        DocumentType::MOTIVATION_LETTER,
        DocumentType::DIPLOMA,
        DocumentType::TRANSCRIPT,
        DocumentType::LANGUAGE_CERTIFICATE,
        DocumentType::RECOMMENDATION_LETTER,
        DocumentType::RESEARCH_PROJECT,
        DocumentType::INTERNSHIP_REPORT,
    ];

    public function __construct(
        private ChecklistService $checklistService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getApplicationsOverview(Candidate $candidate): array
    {
        $campus = $this->getCampusFrance($candidate);
        $parcoursup = $this->getParcoursup($candidate);
        $parisSaclay = $this->getParisSaclay($candidate);
        $documents = $this->getDocuments($candidate);
        $completion = $this->getCompletion($candidate);

        $validatedCount = count(array_filter($documents, static fn (array $d) => 'validated' === $d['status']));
        $missingCount = count(array_filter($documents, static fn (array $d) => in_array($d['status'], ['missing', 'rejected'], true)));

        return [
            'candidateId' => $candidate->getId()->toRfc4122(),
            'referenceNumber' => $candidate->getReferenceNumber(),
            'completion' => $completion,
            'summary' => [
                'documentsValidated' => $validatedCount,
                'documentsMissing' => $missingCount,
                'paymentsPending' => 1,
                'upcomingAppointments' => 0,
            ],
            'counselor' => $this->serializeCounselor($candidate),
            'procedures' => [
                'campusFrance' => $campus['card'],
                'parcoursup' => $parcoursup['card'],
                'parisSaclay' => $parisSaclay['card'],
            ],
            'alerts' => $this->buildAlerts($candidate, $documents, $campus, $parcoursup, $parisSaclay),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCampusFrance(Candidate $candidate): array
    {
        $application = $candidate->getCampusFranceApplication();
        if (!$application instanceof CampusFranceApplication) {
            $application = new CampusFranceApplication();
            $application->setCandidate($candidate);
        }

        $checklist = $this->checklistService->getProgressForCandidate($candidate, ApplicationType::CAMPUS_FRANCE);
        $documents = $this->mapDocumentsForTypes($candidate, self::CAMPUS_FRANCE_DOCUMENT_TYPES);
        $status = $application->getStatus();
        $progress = $this->statusProgress($status, CampusFranceStatus::workflowOrder());

        return [
            'card' => [
                'type' => 'campus_france',
                'label' => 'Campus France',
                'status' => $status->value,
                'statusLabel' => $status->label(),
                'progress' => $progress,
                'updatedAt' => $application->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                'nextAction' => $this->nextCampusFranceAction($status, $documents),
            ],
            'information' => [
                'studyProject' => $application->getStudyProject(),
                'professionalProject' => $application->getProfessionalProject(),
                'targetUniversities' => $application->getTargetUniversities() ?? [],
                'targetPrograms' => $application->getTargetPrograms() ?? [],
            ],
            'documents' => $documents,
            'workflow' => $this->buildCampusFranceWorkflow($status, $checklist['percent']),
            'history' => $this->getTimeline($candidate),
            'messages' => $this->buildMessagesPlaceholder($candidate),
            'checklist' => $checklist,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getParcoursup(Candidate $candidate): array
    {
        $application = $candidate->getParcoursupApplication();
        $wishes = [];
        $progress = 0;

        if ($application instanceof ParcoursupApplication) {
            $wishes = array_map(fn (ParcoursupWish $w) => [
                'id' => $w->getId()->toRfc4122(),
                'rank' => $w->getRank(),
                'formation' => $w->getProgram(),
                'university' => $w->getUniversity(),
                'submittedAt' => $w->getSubmittedAt()?->format(\DateTimeInterface::ATOM),
                'status' => $w->getStatus()->value,
                'statusLabel' => $w->getStatus()->label(),
            ], $application->getWishes()->toArray());

            if ([] !== $wishes) {
                $accepted = count(array_filter($wishes, static fn (array $w) => ParcoursupWishStatus::ACCEPTE->value === $w['status']));
                $progress = (int) round(($accepted / count($wishes)) * 100);
                if (0 === $progress) {
                    $submitted = count(array_filter($wishes, static fn (array $w) => ParcoursupWishStatus::BROUILLON->value !== $w['status']));
                    $progress = (int) round(($submitted / count($wishes)) * 50);
                }
            }
        }

        $documents = $this->mapDocumentsForTypes($candidate, self::PARCOURSUP_DOCUMENT_TYPES);

        return [
            'card' => [
                'type' => 'parcoursup',
                'label' => 'Parcoursup',
                'status' => $application ? 'active' : 'not_started',
                'statusLabel' => $application ? 'En cours' : 'Non démarré',
                'progress' => $progress,
                'updatedAt' => $candidate->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                'nextAction' => [] === $wishes ? 'Ajouter vos vœux Parcoursup' : 'Suivre vos réponses',
            ],
            'information' => $application ? [
                'ineNumber' => $application->getIneNumber(),
                'highSchool' => $application->getHighSchool(),
                'activities' => $application->getActivities(),
                'motivationProject' => $application->getMotivationProject(),
            ] : null,
            'wishes' => $wishes,
            'documents' => $documents,
            'history' => $this->getTimeline($candidate),
            'messages' => $this->buildMessagesPlaceholder($candidate),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getParisSaclay(Candidate $candidate): array
    {
        $application = $candidate->getParisSaclayApplication();
        $status = $application?->getStatus() ?? ParisSaclayStatus::DRAFT;
        $progress = $this->parisSaclayStatusProgress($status);
        $documents = $this->mapDocumentsForTypes($candidate, self::PARIS_SACLAY_DOCUMENT_TYPES);

        return [
            'card' => [
                'type' => 'paris_saclay',
                'label' => 'Paris-Saclay',
                'status' => $status->value,
                'statusLabel' => $status->label(),
                'progress' => $progress,
                'updatedAt' => $candidate->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                'nextAction' => $this->nextParisSaclayAction($status, $documents),
            ],
            'information' => $application ? [
                'degreeLevel' => $application->getDegreeLevel()?->value,
                'degreeLevelLabel' => $application->getDegreeLevel()?->label(),
                'researchProject' => $application->getResearchProject(),
                'publications' => $application->getPublications() ?? [],
            ] : null,
            'documents' => $documents,
            'project' => [
                'researchProject' => $application?->getResearchProject(),
                'internshipReports' => $application?->getInternshipReports() ?? [],
            ],
            'workflow' => $this->buildParisSaclayWorkflow($status),
            'history' => $this->getTimeline($candidate),
            'messages' => $this->buildMessagesPlaceholder($candidate),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDocuments(Candidate $candidate): array
    {
        return array_map(fn (CandidateDocument $doc) => [
            'id' => $doc->getId()->toRfc4122(),
            'type' => $doc->getType()->value,
            'typeLabel' => $doc->getType()->label(),
            'status' => $doc->getStatus()->value,
            'originalFilename' => $doc->getOriginalFilename(),
            'mimeType' => $doc->getMimeType(),
            'size' => $doc->getSize(),
            'uploadedAt' => $doc->getUploadedAt()?->format(\DateTimeInterface::ATOM),
            'validatedAt' => $doc->getValidatedAt()?->format(\DateTimeInterface::ATOM),
        ], $candidate->getDocuments()->toArray());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTimeline(Candidate $candidate): array
    {
        return array_map(static fn (CandidateTimelineEntry $entry) => [
            'id' => $entry->getId()->toRfc4122(),
            'date' => $entry->getOccurredAt()->format('d/m'),
            'occurredAt' => $entry->getOccurredAt()->format(\DateTimeInterface::ATOM),
            'action' => $entry->getAction(),
            'description' => $entry->getDescription(),
            'author' => $entry->getActor() ? trim($entry->getActor()->getFirstName().' '.$entry->getActor()->getLastName()) : 'Système',
            'comment' => $entry->getDescription(),
        ], $candidate->getTimelineEntries()->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function getCompletion(Candidate $candidate): array
    {
        $campusChecklist = $this->checklistService->getProgressForCandidate($candidate, ApplicationType::CAMPUS_FRANCE);
        $parcoursup = $this->getParcoursup($candidate);
        $parisSaclay = $this->getParisSaclay($candidate);

        $profile = min(100, $candidate->getCompletionPercent());
        $documents = $campusChecklist['percent'];
        $campusFrance = $parcoursup['card']['progress'] > 0
            ? (int) round(($campusChecklist['percent'] + $this->statusProgress(
                $candidate->getCampusFranceApplication()?->getStatus() ?? CampusFranceStatus::DRAFT,
                CampusFranceStatus::workflowOrder(),
            )) / 2)
            : $campusChecklist['percent'];

        $visa = match ($candidate->getCampusFranceApplication()?->getStatus()) {
            CampusFranceStatus::VISA_OBTAINED, CampusFranceStatus::COMPLETED => 100,
            CampusFranceStatus::VISA_PENDING => 50,
            default => 0,
        };

        $global = (int) round(($profile + $documents + $campusFrance + $parcoursup['card']['progress'] + $parisSaclay['card']['progress'] + $visa) / 6);

        return [
            'global' => $global,
            'profile' => $profile,
            'documents' => $documents,
            'campusFrance' => $campusFrance,
            'parcoursup' => $parcoursup['card']['progress'],
            'parisSaclay' => $parisSaclay['card']['progress'],
            'visa' => $visa,
        ];
    }

    /**
     * @param list<DocumentType> $types
     *
     * @return list<array<string, mixed>>
     */
    private function mapDocumentsForTypes(Candidate $candidate, array $types): array
    {
        $byType = [];
        foreach ($candidate->getDocuments() as $document) {
            $byType[$document->getType()->value] = $document;
        }

        $result = [];
        foreach ($types as $type) {
            $document = $byType[$type->value] ?? null;
            $result[] = [
                'type' => $type->value,
                'label' => $type->label(),
                'status' => $this->documentIndicatorStatus($document),
                'documentId' => $document?->getId()->toRfc4122(),
                'originalFilename' => $document?->getOriginalFilename(),
                'uploadedAt' => $document?->getUploadedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $result;
    }

    private function documentIndicatorStatus(?CandidateDocument $document): string
    {
        if (null === $document) {
            return 'missing';
        }

        return match ($document->getStatus()) {
            DocumentStatus::VALIDATED => 'validated',
            DocumentStatus::REJECTED => 'rejected',
            DocumentStatus::UPLOADED => 'pending',
            default => 'missing',
        };
    }

    /**
     * @param list<CampusFranceStatus> $order
     */
    private function statusProgress(CampusFranceStatus $current, array $order): int
    {
        $values = array_map(static fn (CampusFranceStatus $s) => $s->value, $order);
        $index = array_search($current->value, $values, true);

        if (false === $index) {
            return 0;
        }

        return (int) round((($index + 1) / count($order)) * 100);
    }

    private function parisSaclayStatusProgress(ParisSaclayStatus $current): int
    {
        $order = ParisSaclayStatus::workflowOrder();
        $values = array_map(static fn (ParisSaclayStatus $s) => $s->value, $order);
        $index = array_search($current->value, $values, true);

        if (false === $index) {
            return 0;
        }

        return (int) round((($index + 1) / count($order)) * 100);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildCampusFranceWorkflow(CampusFranceStatus $status, int $documentsPercent): array
    {
        $statusIndex = $this->workflowIndex($status, CampusFranceStatus::workflowOrder());

        return array_map(static function (array $step, int $index) use ($statusIndex, $documentsPercent) {
            $state = 'upcoming';
            if ($index < $statusIndex) {
                $state = 'done';
            } elseif ($index === $statusIndex) {
                $state = 'current';
            }
            if ('documents_received' === $step['key'] && $documentsPercent > 0 && 'upcoming' === $state) {
                $state = 'current';
            }
            if ('documents_validated' === $step['key'] && $documentsPercent >= 100) {
                $state = 'done';
            }

            return array_merge($step, ['state' => $state]);
        }, self::CAMPUS_FRANCE_WORKFLOW, array_keys(self::CAMPUS_FRANCE_WORKFLOW));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildParisSaclayWorkflow(ParisSaclayStatus $status): array
    {
        $order = [
            ParisSaclayStatus::DRAFT,
            ParisSaclayStatus::PENDING_DOCUMENTS,
            ParisSaclayStatus::PENDING_DOCUMENTS,
            ParisSaclayStatus::SUBMITTED,
            ParisSaclayStatus::UNDER_REVIEW,
            ParisSaclayStatus::ADMISSION_OBTAINED,
            ParisSaclayStatus::VISA_PENDING,
            ParisSaclayStatus::COMPLETED,
        ];
        $statusIndex = $this->workflowIndex($status, $order);

        return array_map(static function (array $step, int $index) use ($statusIndex) {
            $state = 'upcoming';
            if ($index < $statusIndex) {
                $state = 'done';
            } elseif ($index === $statusIndex) {
                $state = 'current';
            }

            return array_merge($step, ['state' => $state]);
        }, self::PARIS_SACLAY_WORKFLOW, array_keys(self::PARIS_SACLAY_WORKFLOW));
    }

    /**
     * @param list<CampusFranceStatus|ParisSaclayStatus> $order
     */
    private function workflowIndex(CampusFranceStatus|ParisSaclayStatus $status, array $order): int
    {
        foreach ($order as $index => $step) {
            if ($step === $status) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @param list<array<string, mixed>> $documents
     */
    private function nextCampusFranceAction(CampusFranceStatus $status, array $documents): string
    {
        $missing = count(array_filter($documents, static fn (array $d) => 'missing' === $d['status']));
        if ($missing > 0) {
            return sprintf('Téléverser %d document(s) manquant(s)', $missing);
        }

        return match ($status) {
            CampusFranceStatus::DRAFT, CampusFranceStatus::PENDING_DOCUMENTS => 'Finaliser votre dossier Campus France',
            CampusFranceStatus::SUBMITTED => 'Attendre la convocation à l\'entretien',
            CampusFranceStatus::INTERVIEW_SCHEDULED => 'Préparer votre entretien pédagogique',
            CampusFranceStatus::ADMISSION_OBTAINED => 'Lancer la demande de visa',
            CampusFranceStatus::VISA_PENDING => 'Suivre votre demande de visa',
            default => 'Consulter votre progression',
        };
    }

    /**
     * @param list<array<string, mixed>> $documents
     */
    private function nextParisSaclayAction(ParisSaclayStatus $status, array $documents): string
    {
        $missing = count(array_filter($documents, static fn (array $d) => 'missing' === $d['status']));
        if ($missing > 0) {
            return sprintf('Compléter %d document(s)', $missing);
        }

        return match ($status) {
            ParisSaclayStatus::DRAFT => 'Renseigner votre projet de recherche',
            ParisSaclayStatus::SUBMITTED => 'Suivre l\'étude de votre dossier',
            ParisSaclayStatus::ADMISSION_OBTAINED => 'Préparer votre arrivée',
            default => 'Consulter votre candidature',
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializeCounselor(Candidate $candidate): ?array
    {
        $counselor = $candidate->getAssignedCounselor();
        if (!$counselor instanceof User) {
            return null;
        }

        return [
            'id' => $counselor->getId()->toRfc4122(),
            'firstName' => $counselor->getFirstName(),
            'lastName' => $counselor->getLastName(),
            'email' => $counselor->getEmail(),
            'fullName' => trim($counselor->getFirstName().' '.$counselor->getLastName()),
        ];
    }

    /**
     * @param list<array<string, mixed>> $documents
     * @param array<string, mixed>       $campus
     * @param array<string, mixed>       $parcoursup
     * @param array<string, mixed>       $parisSaclay
     *
     * @return list<array<string, mixed>>
     */
    private function buildAlerts(
        Candidate $candidate,
        array $documents,
        array $campus,
        array $parcoursup,
        array $parisSaclay,
    ): array {
        $alerts = [];

        $missing = count(array_filter($documents, static fn (array $d) => 'missing' === $d['status']));
        if ($missing > 0) {
            $alerts[] = ['type' => 'missing_documents', 'severity' => 'warning', 'message' => sprintf('%d document(s) manquant(s)', $missing)];
        }

        $rejected = count(array_filter($documents, static fn (array $d) => 'rejected' === $d['status']));
        if ($rejected > 0) {
            $alerts[] = ['type' => 'rejected_document', 'severity' => 'error', 'message' => sprintf('%d document(s) refusé(s)', $rejected)];
        }

        if (CampusFranceStatus::VISA_PENDING->value === ($campus['card']['status'] ?? '')) {
            $alerts[] = ['type' => 'visa_pending', 'severity' => 'info', 'message' => 'Votre demande de visa est en cours'];
        }

        $alerts[] = ['type' => 'payment_pending', 'severity' => 'warning', 'message' => 'Paiement en attente (placeholder)'];

        return $alerts;
    }

    /**
     * @return array{threads: list<array<string, mixed>>, readOnly: true}
     */
    private function buildMessagesPlaceholder(Candidate $candidate): array
    {
        return [
            'readOnly' => true,
            'threads' => [
                [
                    'id' => 'counselor-main',
                    'participant' => $this->serializeCounselor($candidate),
                    'messages' => [],
                    'note' => 'La messagerie temps réel sera disponible prochainement.',
                ],
            ],
        ];
    }
}
