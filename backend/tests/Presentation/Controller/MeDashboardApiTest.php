<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MeDashboardApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCandidateCanLoadAggregatedDashboard(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedChecklist();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/dashboard', server: $this->jsonAuthHeaders($auth));

        $this->assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('status', $payload);
        self::assertArrayHasKey('checklist', $payload);
        self::assertArrayHasKey('documents', $payload);
        self::assertIsArray($payload['checklist']['items']);
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
