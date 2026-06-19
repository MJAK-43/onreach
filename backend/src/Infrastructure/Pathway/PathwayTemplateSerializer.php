<?php

declare(strict_types=1);

namespace App\Infrastructure\Pathway;

use App\Entity\PathwayStageTemplate;
use App\Entity\PathwaySubStepTemplate;
use App\Entity\PathwayTemplate;

final readonly class PathwayTemplateSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serializeSummary(PathwayTemplate $template): array
    {
        $stageCount = $template->getStages()->count();
        $subStepCount = 0;
        foreach ($template->getStages() as $stage) {
            $subStepCount += $stage->getSubSteps()->count();
        }

        return [
            'id' => $template->getId()->toRfc4122(),
            'code' => $template->getCode()->value,
            'name' => $template->getName(),
            'campaignId' => $template->getCampaign()->getId()->toRfc4122(),
            'campaignYear' => $template->getCampaign()->getYear(),
            'stageCount' => $stageCount,
            'subStepCount' => $subStepCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeDetail(PathwayTemplate $template): array
    {
        return [
            ...$this->serializeSummary($template),
            'eligibleStudyTypes' => array_map(
                static fn ($type) => $type->value,
                $template->getEligibleStudyTypes(),
            ),
            'stages' => array_map(
                fn (PathwayStageTemplate $stage) => [
                    'id' => $stage->getId()->toRfc4122(),
                    'title' => $stage->getTitle(),
                    'description' => $stage->getDescription(),
                    'sortOrder' => $stage->getSortOrder(),
                    'subSteps' => array_map(
                        fn (PathwaySubStepTemplate $subStep) => [
                            'id' => $subStep->getId()->toRfc4122(),
                            'title' => $subStep->getTitle(),
                            'description' => $subStep->getDescription(),
                            'required' => $subStep->isRequired(),
                            'defaultDueOffsetDays' => $subStep->getDefaultDueOffsetDays(),
                            'sortOrder' => $subStep->getSortOrder(),
                        ],
                        $stage->getSubSteps()->toArray(),
                    ),
                ],
                $template->getStages()->toArray(),
            ),
        ];
    }
}
