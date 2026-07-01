<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Candidate;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\User;
use App\Repository\CandidatePathwayRepository;
use App\Repository\PathwaySettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PathwaySubStepService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PathwaySettingRepository $settingRepository,
        private CandidatePathwayRepository $pathwayRepository,
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
        $setting = $this->settingRepository->findOneByPathwayCode($pathway->getPathwayTemplate()->getCode());
        $context = PathwayValidationContext::fromSetting($setting);

        $counselorBefore = null !== $subStep->getCounselorValidatedAt();
        $adminBefore = null !== $subStep->getAdminValidatedAt();
        $validatedBefore = $context->isSubStepValidated($subStep);

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

        $this->syncGrandfatheredValidation($subStep, $context->doubleValidationEnabled);
        $this->entityManager->flush();

        $pathwayForProgress = $this->pathwayRepository->findOneForProgressRefresh($pathway->getId());
        if ($pathwayForProgress) {
            $this->progressCalculator->refresh($pathwayForProgress);
        }

        $this->auditLogger->log(
            $candidate,
            'substep.validation_updated',
            $pathwayForProgress ?? $pathway,
            $subStep,
            [
                'validated' => $context->isSubStepValidated($subStep),
                'subStepTitle' => $subStep->getSubStepTemplate()->getTitle(),
            ],
            $actor,
        );
        $this->entityManager->flush();

        $this->eventNotifier->notifyValidationUpdated(
            $candidate,
            $pathwayForProgress ?? $pathway,
            $subStep,
            $actor,
            $context,
            $counselorBefore,
            $adminBefore,
            $validatedBefore,
        );
        $this->entityManager->flush();

        return $subStep;
    }

    private function syncGrandfatheredValidation(CandidatePathwaySubStep $subStep, bool $doubleValidation): void
    {
        if (null === $subStep->getCounselorValidatedAt()) {
            $subStep->setGrandfatheredValidation(false);

            return;
        }

        if (!$doubleValidation) {
            $subStep->setGrandfatheredValidation(true);

            return;
        }

        if (null !== $subStep->getAdminValidatedAt()) {
            $subStep->setGrandfatheredValidation(false);
        }
    }
}
