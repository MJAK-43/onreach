<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Candidate;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\User;
use App\Repository\PathwaySettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PathwaySubStepService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PathwaySettingRepository $settingRepository,
        private PathwayProgressCalculator $progressCalculator,
        private PathwayAuditLogger $auditLogger,
        private PathwayEventNotifier $eventNotifier,
    ) {
    }

    /**
     * @param array{validated?: bool, counselorValidated?: bool, adminValidated?: bool} $payload
     */
    public function updateValidation(
        Candidate $candidate,
        CandidatePathwaySubStep $subStep,
        array $payload,
        User $actor,
    ): CandidatePathwaySubStep {
        if ($subStep->getCandidateStage()->getCandidatePathway()->getCandidate()->getId()->toRfc4122()
            !== $candidate->getId()->toRfc4122()) {
            throw new NotFoundHttpException('Sous-étape introuvable.');
        }

        $pathway = $subStep->getCandidateStage()->getCandidatePathway();
        $doubleValidation = $this->settingRepository
            ->findOneByPathwayCode($pathway->getPathwayTemplate()->getCode())
            ?->isDoubleValidationEnabled() ?? false;

        $counselorBefore = null !== $subStep->getCounselorValidatedAt();
        $adminBefore = null !== $subStep->getAdminValidatedAt();
        $validatedBefore = $subStep->isValidated($doubleValidation);

        $isAdmin = $actor->hasRole('ADMIN') || $actor->hasRole('SUPER_ADMIN');
        $isCounselor = $actor->hasRole('COUNSELOR') || $isAdmin;

        if (isset($payload['validated'])) {
            if ((bool) $payload['validated']) {
                $canValidate = $actor->hasRole('COUNSELOR')
                    || $actor->hasRole('ADMIN')
                    || $actor->hasRole('SUPER_ADMIN');
                if (!$canValidate) {
                    throw new BadRequestHttpException('Validation non autorisée pour ce rôle.');
                }
                if ($isCounselor) {
                    $subStep->validateByCounselor($actor);
                }
                if ($isAdmin) {
                    $subStep->validateByAdmin($actor);
                }
            } else {
                $subStep->clearValidation();
            }
        } else {
            if (isset($payload['counselorValidated'])) {
                if ((bool) $payload['counselorValidated']) {
                    if (!$isCounselor) {
                        throw new BadRequestHttpException('Validation conseiller non autorisée.');
                    }
                    $subStep->validateByCounselor($actor);
                } else {
                    $subStep->clearCounselorValidation();
                }
            }
            if (isset($payload['adminValidated'])) {
                if ((bool) $payload['adminValidated']) {
                    if (!$isAdmin) {
                        throw new BadRequestHttpException('Validation admin non autorisée.');
                    }
                    $subStep->validateByAdmin($actor);
                } else {
                    $subStep->clearAdminValidation();
                }
            }
        }

        $this->progressCalculator->refresh($pathway);
        $this->auditLogger->log(
            $candidate,
            'substep.validation_updated',
            $pathway,
            $subStep,
            [
                'validated' => $subStep->isValidated($doubleValidation),
                'subStepTitle' => $subStep->getSubStepTemplate()->getTitle(),
            ],
            $actor,
        );
        $this->entityManager->flush();

        $this->eventNotifier->notifyValidationUpdated(
            $candidate,
            $pathway,
            $subStep,
            $actor,
            $doubleValidation,
            $counselorBefore,
            $adminBefore,
            $validatedBefore,
        );
        $this->entityManager->flush();

        return $subStep;
    }
}
