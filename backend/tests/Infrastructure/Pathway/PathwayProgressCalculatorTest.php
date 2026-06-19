<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Pathway;

use App\Domain\Pathway\Enum\StudyApplicationType;
use App\Entity\Candidate;
use App\Entity\CandidatePathwaySubStep;
use App\Entity\User;
use App\Infrastructure\Pathway\PathwayAssignmentService;
use App\Infrastructure\Pathway\PathwaySubStepService;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PathwayProgressCalculatorTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();

        parent::tearDown();
    }

    public function testFirstYearGetsParcoursupAndCampusFrance(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();

        $em = static::getContainer()->get('doctrine')->getManager();
        $candidate = new Candidate('Test', 'User', 'pathway@test.fr', 'France');
        $candidate->setStudyApplicationType(StudyApplicationType::FIRST_YEAR);
        $em->persist($candidate);
        $em->flush();

        $assignment = static::getContainer()->get(PathwayAssignmentService::class);
        $assignment->assignForCandidate($candidate);

        $pathways = $assignment->listForCandidate($candidate);
        self::assertCount(2, $pathways);

        $codes = array_map(static fn ($p) => $p->getPathwayTemplate()->getCode()->value, $pathways);
        self::assertContains('parcoursup', $codes);
        self::assertContains('campus_france', $codes);
        self::assertNotEmpty($pathways[0]->getStages());
    }

    public function testValidationUpdatesProgress(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();

        $candidate = new Candidate('Progress', 'Test', 'progress@test.fr', 'France');
        $candidate->setStudyApplicationType(StudyApplicationType::CONTINUING);

        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($candidate);
        $em->flush();

        $assignment = static::getContainer()->get(PathwayAssignmentService::class);
        $assignment->assignForCandidate($candidate);
        $pathways = $assignment->listForCandidate($candidate);
        self::assertNotEmpty($pathways);

        $pathway = $pathways[0];
        $stage = $pathway->getStages()->first();
        self::assertNotFalse($stage);
        $subStep = $stage->getSubSteps()->first();
        self::assertInstanceOf(CandidatePathwaySubStep::class, $subStep);

        $userRepo = static::getContainer()->get(\App\Repository\UserRepository::class);
        $admin = $userRepo->findByEmail('admin@onreach.inovixora.fr');
        self::assertInstanceOf(User::class, $admin);

        $subStepService = static::getContainer()->get(PathwaySubStepService::class);
        $subStepService->updateValidation($candidate, $subStep, ['validated' => true], $admin);

        $refreshed = $assignment->listForCandidate($candidate)[0];
        self::assertGreaterThan(0, $refreshed->getProgressPercent());
    }
}
