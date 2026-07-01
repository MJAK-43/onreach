<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Pathway;

use App\Entity\Campaign;
use App\Entity\Candidate;
use App\Entity\CandidatePathway;
use App\Entity\CandidatePathwayStage;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\PathwaySubStepTemplate;
use App\Infrastructure\Pathway\PathwayDueDateSyncService;
use App\Repository\CampaignRepository;
use App\Repository\PathwaySubStepTemplateRepository;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PathwayDueDateSyncServiceTest extends KernelTestCase
{
    use DatabaseTestTrait;

    public function testSyncTemplateSubStepPropagatesDueDateToCandidates(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();

        $container = static::getContainer();
        $campaign = $container->get(CampaignRepository::class)->findOneBy(['active' => true]);
        self::assertInstanceOf(Campaign::class, $campaign);

        $subStepTemplate = $container->get(PathwaySubStepTemplateRepository::class)->findOneBy([]);
        self::assertInstanceOf(PathwaySubStepTemplate::class, $subStepTemplate);
        $subStepTemplate->setDefaultDueOffsetDays(140);
        $container->get(\Doctrine\ORM\EntityManagerInterface::class)->flush();

        $candidate = new Candidate('Test', 'Candidat', 'sync.test@example.com', 'Sénégal', 'ONR-SYNC-001');
        $em = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
        $em->persist($candidate);

        $pathwayTemplate = $subStepTemplate->getStageTemplate()->getPathwayTemplate();
        $candidatePathway = new CandidatePathway($candidate, $pathwayTemplate);
        $em->persist($candidatePathway);

        $stageTemplate = $subStepTemplate->getStageTemplate();
        $candidateStage = new CandidatePathwayStage($candidatePathway, $stageTemplate, $stageTemplate->getSortOrder());
        $em->persist($candidateStage);

        $candidateSubStep = new CandidatePathwaySubStep(
            $candidateStage,
            $subStepTemplate,
            $subStepTemplate->getSortOrder(),
        );
        $em->persist($candidateSubStep);
        $em->flush();

        $synced = $container->get(PathwayDueDateSyncService::class)->syncTemplateSubStep($subStepTemplate);
        self::assertGreaterThanOrEqual(1, $synced);

        $em->refresh($candidateSubStep);
        $expected = $campaign->getStartDate()->modify('+140 days')->format('Y-m-d');
        self::assertSame($expected, $candidateSubStep->getDueDate()?->format('Y-m-d'));
    }
}
