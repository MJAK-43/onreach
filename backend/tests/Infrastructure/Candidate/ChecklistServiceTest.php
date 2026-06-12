<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Candidate;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Domain\Candidate\Enum\DocumentStatus;
use App\Domain\Candidate\Enum\DocumentType;
use App\Entity\Candidate;
use App\Entity\CandidateDocument;
use App\Infrastructure\Candidate\ChecklistService;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ChecklistServiceTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();

        parent::tearDown();
    }

    public function testProgressIncreasesWhenDocumentValidated(): void
    {
        self::bootKernel();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();

        $candidate = new Candidate('Jean', 'Dupont', 'jean@example.com', 'France');
        $candidate->addDocument(new CandidateDocument($candidate, DocumentType::PASSPORT, DocumentStatus::VALIDATED));

        $service = static::getContainer()->get(ChecklistService::class);
        $progress = $service->getProgressForCandidate($candidate, ApplicationType::CAMPUS_FRANCE);

        self::assertGreaterThan(0, $progress['percent']);
        self::assertTrue($progress['items'][0]['completed']);
    }
}
