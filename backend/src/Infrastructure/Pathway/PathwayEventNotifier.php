<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\PathwayInstanceStatus;
use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\User;
use App\Infrastructure\Mail\PathwayValidationMailer;
use App\Infrastructure\Notification\InAppNotificationService;
use App\Repository\UserRepository;

final readonly class PathwayEventNotifier
{
    public function __construct(
        private InAppNotificationService $notificationService,
        private PathwayValidationMailer $mailer,
        private UserRepository $userRepository,
        private string $frontendUrl,
    ) {
    }

    public function notifyValidationUpdated(
        Candidate $candidate,
        CandidatePathway $pathway,
        CandidatePathwaySubStep $subStep,
        User $actor,
        PathwayValidationContext $context,
        bool $counselorBefore,
        bool $adminBefore,
        bool $validatedBefore,
    ): void {
        $pathwayName = $pathway->getPathwayTemplate()->getName();
        $subStepTitle = $subStep->getSubStepTemplate()->getTitle();
        $counselorNow = null !== $subStep->getCounselorValidatedAt();
        $adminNow = null !== $subStep->getAdminValidatedAt();
        $validatedNow = $context->isSubStepValidated($subStep);
        $candidateUser = $this->userRepository->findByEmail($candidate->getEmail());

        if (!$counselorBefore && $counselorNow && $context->doubleValidationEnabled) {
            foreach ($this->findAdminUsers() as $admin) {
                if ($admin->getId()->toRfc4122() === $actor->getId()->toRfc4122()) {
                    continue;
                }

                $this->notificationService->notify(
                    $admin,
                    'pathway.admin_validation_required',
                    'Validation admin requise',
                    sprintf(
                        '%s — %s : %s',
                        trim($candidate->getFirstName().' '.$candidate->getLastName()),
                        $pathwayName,
                        $subStepTitle,
                    ),
                    sprintf('%s/candidates/%s', rtrim($this->frontendUrl, '/'), $candidate->getId()->toRfc4122()),
                    [
                        'candidateId' => $candidate->getId()->toRfc4122(),
                        'pathwayId' => $pathway->getId()->toRfc4122(),
                        'subStepId' => $subStep->getId()->toRfc4122(),
                    ],
                );

                $this->mailer->notifyAdminValidationRequired($admin, $candidate, $pathwayName, $subStepTitle);
            }
        }

        if (!$validatedBefore && $validatedNow && $candidateUser) {
            $this->notificationService->notify(
                $candidateUser,
                'pathway.substep_validated',
                'Étape validée',
                sprintf('%s — %s', $pathwayName, $subStepTitle),
                rtrim($this->frontendUrl, '/').'/demarches',
                [
                    'pathwayCode' => $pathway->getPathwayTemplate()->getCode()->value,
                    'subStepId' => $subStep->getId()->toRfc4122(),
                ],
            );

            $this->mailer->notifyCandidateStepValidated($candidate, $pathwayName, $subStepTitle);
        }

        if ($adminBefore !== $adminNow && $adminNow && !$validatedNow && $candidateUser) {
            $this->notificationService->notify(
                $candidateUser,
                'pathway.substep_pending_admin',
                'Validation en cours',
                sprintf('%s — %s : en attente de validation administrateur', $pathwayName, $subStepTitle),
                rtrim($this->frontendUrl, '/').'/demarches',
            );
        }
    }

    public function notifyPathwayStatusUpdated(
        Candidate $candidate,
        CandidatePathway $pathway,
        User $actor,
        PathwayInstanceStatus $previousStatus,
    ): void {
        if ($previousStatus === $pathway->getStatus()) {
            return;
        }

        $candidateUser = $this->userRepository->findByEmail($candidate->getEmail());
        $pathwayName = $pathway->getPathwayTemplate()->getName();
        $message = sprintf(
            '%s — statut : %s',
            $pathwayName,
            $pathway->getStatus()->label(),
        );

        if (PathwayInstanceStatus::BLOCKED === $pathway->getStatus() && $pathway->getBlockedReason()) {
            $message .= sprintf(' (%s)', $pathway->getBlockedReason());
        }

        if ($candidateUser) {
            $this->notificationService->notify(
                $candidateUser,
                'pathway.status_updated',
                'Statut parcours mis à jour',
                $message,
                rtrim($this->frontendUrl, '/').'/demarches',
                [
                    'pathwayId' => $pathway->getId()->toRfc4122(),
                    'status' => $pathway->getStatus()->value,
                ],
            );
        }

        $counselor = $candidate->getAssignedCounselor();
        if ($counselor && $counselor->getId()->toRfc4122() !== $actor->getId()->toRfc4122()) {
            $this->notificationService->notify(
                $counselor,
                'pathway.status_updated',
                'Statut parcours mis à jour',
                sprintf(
                    '%s — %s',
                    trim($candidate->getFirstName().' '.$candidate->getLastName()),
                    $message,
                ),
                sprintf('%s/candidates/%s', rtrim($this->frontendUrl, '/'), $candidate->getId()->toRfc4122()),
            );
        }
    }

    public function notifyDueDateReminder(CandidatePathwaySubStep $subStep): void
    {
        $pathway = $subStep->getCandidateStage()->getCandidatePathway();
        $candidate = $pathway->getCandidate();
        $pathwayName = $pathway->getPathwayTemplate()->getName();
        $subStepTitle = $subStep->getSubStepTemplate()->getTitle();
        $dueDate = $subStep->getDueDate();
        $dueLabel = $dueDate?->format('d/m/Y') ?? '';
        $isOverdue = $dueDate && $dueDate < new \DateTimeImmutable('today');
        $title = $isOverdue ? 'Échéance dépassée' : 'Échéance proche';
        $body = sprintf('%s — %s (%s)', $pathwayName, $subStepTitle, $dueLabel);

        $candidateUser = $this->userRepository->findByEmail($candidate->getEmail());
        if ($candidateUser) {
            $this->notificationService->notify(
                $candidateUser,
                'pathway.due_date_reminder',
                $title,
                $body,
                rtrim($this->frontendUrl, '/').'/demarches',
                ['subStepId' => $subStep->getId()->toRfc4122()],
            );
        }

        $counselor = $candidate->getAssignedCounselor();
        if ($counselor) {
            $this->notificationService->notify(
                $counselor,
                'pathway.due_date_reminder',
                $title,
                sprintf('%s — %s', trim($candidate->getFirstName().' '.$candidate->getLastName()), $body),
                sprintf('%s/candidates/%s', rtrim($this->frontendUrl, '/'), $candidate->getId()->toRfc4122()),
            );
        }
    }

    /**
     * @return list<User>
     */
    private function findAdminUsers(): array
    {
        /** @var list<User> $users */
        $users = $this->userRepository->createQueryBuilder('u')
            ->innerJoin('u.roles', 'r')
            ->andWhere('r.code IN (:codes)')
            ->andWhere('u.isActive = true')
            ->setParameter('codes', ['ADMIN', 'SUPER_ADMIN'])
            ->getQuery()
            ->getResult();

        return $users;
    }
}
