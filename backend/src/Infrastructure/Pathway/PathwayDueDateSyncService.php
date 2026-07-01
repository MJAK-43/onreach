<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\Campaign;
use App\Entity\PathwaySubStepTemplate;
use App\Repository\CandidatePathwaySubStepRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PathwayDueDateSyncService
{
    public function __construct(
        private CandidatePathwaySubStepRepository $subStepRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function computeDueDate(Campaign $campaign, PathwaySubStepTemplate $template): ?\DateTimeImmutable
    {
        $offset = $template->getDefaultDueOffsetDays();
        if (null === $offset) {
            return null;
        }

        return $campaign->getStartDate()->modify(sprintf('+%d days', $offset));
    }

    public function syncTemplateSubStep(PathwaySubStepTemplate $template): int
    {
        $campaign = $template->getStageTemplate()->getPathwayTemplate()->getCampaign();
        $dueDate = $this->computeDueDate($campaign, $template);

        return $this->applyDueDateToMatches($template, $dueDate);
    }

    public function syncCampaign(Campaign $campaign): int
    {
        $synced = 0;

        foreach ($campaign->getPathwayTemplates() as $template) {
            foreach ($template->getStages() as $stage) {
                foreach ($stage->getSubSteps() as $subStep) {
                    $synced += $this->syncTemplateSubStep($subStep);
                }
            }
        }

        return $synced;
    }

    public function syncTemplate(Campaign $campaign, \App\Entity\PathwayTemplate $template): int
    {
        $synced = 0;

        foreach ($template->getStages() as $stage) {
            foreach ($stage->getSubSteps() as $subStep) {
                $dueDate = $this->computeDueDate($campaign, $subStep);
                $synced += $this->applyDueDateToMatches($subStep, $dueDate);
            }
        }

        return $synced;
    }

    private function applyDueDateToMatches(PathwaySubStepTemplate $template, ?\DateTimeImmutable $dueDate): int
    {
        $subSteps = $this->subStepRepository->findBySubStepTemplate($template);
        $synced = 0;

        foreach ($subSteps as $subStep) {
            if ($this->datesEqual($subStep->getDueDate(), $dueDate)) {
                continue;
            }

            $subStep->setDueDate($dueDate);
            ++$synced;
        }

        if ($synced > 0) {
            $this->entityManager->flush();
        }

        return $synced;
    }

    private function datesEqual(?\DateTimeImmutable $left, ?\DateTimeImmutable $right): bool
    {
        if (!$left instanceof \DateTimeImmutable && !$right instanceof \DateTimeImmutable) {
            return true;
        }

        if (!$left instanceof \DateTimeImmutable || !$right instanceof \DateTimeImmutable) {
            return false;
        }

        return $left->format('Y-m-d') === $right->format('Y-m-d');
    }
}
