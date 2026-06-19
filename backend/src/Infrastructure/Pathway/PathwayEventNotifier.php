<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

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
        bool $doubleValidation,
        bool $counselorBefore,
        bool $adminBefore,
        bool $validatedBefore,
    ): void {
        $pathwayName = $pathway->getPathwayTemplate()->getName();
        $subStepTitle = $subStep->getSubStepTemplate()->getTitle();
        $counselorNow = null !== $subStep->getCounselorValidatedAt();
        $adminNow = null !== $subStep->getAdminValidatedAt();
        $validatedNow = $subStep->isValidated($doubleValidation);
        $candidateUser = $this->userRepository->findByEmail($candidate->getEmail());

        if (!$counselorBefore && $counselorNow && $doubleValidation) {
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
