<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\StudyApplicationType;
use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwayStage;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\Campaign;
use App\Entity\PathwayStageTemplate;
use App\Entity\PathwaySubStepTemplate;
use App\Entity\PathwayTemplate;
use App\Repository\CampaignRepository;
use App\Repository\CandidatePathwayRepository;
use App\Repository\PathwayTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PathwayAssignmentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CampaignRepository $campaignRepository,
        private PathwayTemplateRepository $pathwayTemplateRepository,
        private CandidatePathwayRepository $candidatePathwayRepository,
        private PathwayProgressCalculator $progressCalculator,
        private PathwayAuditLogger $auditLogger,
    ) {
    }

    public function assignForCandidate(Candidate $candidate, ?StudyApplicationType $previousType = null): void
    {
        $studyType = $candidate->getStudyApplicationType();
        if (!$studyType instanceof StudyApplicationType) {
            return;
        }

        if ($previousType instanceof StudyApplicationType && $previousType === $studyType) {
            return;
        }

        $campaign = $this->campaignRepository->findActive()
            ?? $this->campaignRepository->findOneBy(['year' => 2026, 'active' => true]);
        if (!$campaign instanceof Campaign) {
            return;
        }

        if ($previousType instanceof StudyApplicationType && $previousType !== $studyType) {
            $this->removePathwaysForCandidate($candidate);
        }

        foreach (PathwayTemplateDefinitions::codesForStudyType($studyType) as $code) {
            $template = $this->findTemplateForCampaign($campaign, $code);
            if (!$template instanceof PathwayTemplate) {
                continue;
            }

            if ($this->hasPathway($candidate, $template)) {
                continue;
            }

            $pathway = $this->instantiatePathway($candidate, $template, $campaign);
            $this->entityManager->persist($pathway);
            $candidate->addPathway($pathway);
            $this->auditLogger->log($candidate, 'pathway.assigned', $pathway, null, [
                'pathwayCode' => $code->value,
                'studyApplicationType' => $studyType->value,
            ]);
        }

        $this->entityManager->flush();
    }

    private function hasPathway(Candidate $candidate, PathwayTemplate $template): bool
    {
        foreach ($candidate->getPathways() as $pathway) {
            if ($pathway->getPathwayTemplate()->getId()->equals($template->getId())) {
                return true;
            }
        }

        return false;
    }

    private function removePathwaysForCandidate(Candidate $candidate): void
    {
        foreach ($candidate->getPathways()->toArray() as $pathway) {
            $candidate->removePathway($pathway);
            $this->entityManager->remove($pathway);
        }
    }

    private function instantiatePathway(Candidate $candidate, PathwayTemplate $template, Campaign $campaign): CandidatePathway
    {
        $pathway = new CandidatePathway($candidate, $template);

        foreach ($template->getStages() as $stageTemplate) {
            $stage = $this->instantiateStage($pathway, $stageTemplate, $campaign);
            $pathway->addStage($stage);
            $this->entityManager->persist($stage);
        }

        $this->progressCalculator->refresh($pathway);

        return $pathway;
    }

    private function instantiateStage(
        CandidatePathway $pathway,
        PathwayStageTemplate $stageTemplate,
        Campaign $campaign,
    ): CandidatePathwayStage {
        $stage = new CandidatePathwayStage($pathway, $stageTemplate, $stageTemplate->getSortOrder());

        foreach ($stageTemplate->getSubSteps() as $subStepTemplate) {
            $subStep = $this->instantiateSubStep($stage, $subStepTemplate, $campaign);
            $stage->addSubStep($subStep);
            $this->entityManager->persist($subStep);
        }

        return $stage;
    }

    private function instantiateSubStep(
        CandidatePathwayStage $stage,
        PathwaySubStepTemplate $subStepTemplate,
        Campaign $campaign,
    ): CandidatePathwaySubStep {
        $dueDate = null;
        $offset = $subStepTemplate->getDefaultDueOffsetDays();
        if (null !== $offset) {
            $dueDate = $campaign->getStartDate()->modify(sprintf('+%d days', $offset));
        }

        return new CandidatePathwaySubStep(
            $stage,
            $subStepTemplate,
            $subStepTemplate->getSortOrder(),
            $dueDate,
        );
    }

    /** @return list<CandidatePathway> */
    public function listForCandidate(Candidate $candidate): array
    {
        return $this->candidatePathwayRepository->findByCandidate($candidate);
    }

    private function findTemplateForCampaign(Campaign $campaign, \App\Domain\Pathway\Enum\PathwayCode $code): ?PathwayTemplate
    {
        $template = $this->pathwayTemplateRepository->createQueryBuilder('pt')
            ->innerJoin('pt.stages', 's')->addSelect('s')
            ->innerJoin('s.subSteps', 'ss')->addSelect('ss')
            ->where('IDENTITY(pt.campaign) = :campaignId')
            ->andWhere('pt.code = :code')
            ->setParameter('campaignId', $campaign->getId(), 'uuid')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $template instanceof PathwayTemplate ? $template : null;
    }
}
