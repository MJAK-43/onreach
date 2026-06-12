<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MeApplicationsApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCandidateCanAccessMeApplicationsEndpoints(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);

        foreach ([
            '/api/me/campus-france',
            '/api/me/parcoursup',
            '/api/me/paris-saclay',
            '/api/me/documents',
            '/api/me/timeline',
        ] as $path) {
            $client->request('GET', $path, server: $this->jsonAuthHeaders($auth));
            $this->assertResponseIsSuccessful(sprintf('Échec sur %s', $path));
        }

        $client->request('GET', '/api/me/applications', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();
        $applications = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('completion', $applications);
        self::assertArrayHasKey('procedures', $applications);
        self::assertArrayHasKey('campusFrance', $applications['procedures']);
        self::assertArrayHasKey('parcoursup', $applications['procedures']);
        self::assertArrayHasKey('parisSaclay', $applications['procedures']);
    }

    public function testAdminCannotAccessMeApplications(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();

        $auth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/me/applications', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testCandidateTimelineContainsEntries(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/timeline', server: $this->jsonAuthHeaders($auth));
        $timeline = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($timeline);
        self::assertArrayHasKey('description', $timeline[0]);
    }

    public function testParcoursupReturnsWishes(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/parcoursup', server: $this->jsonAuthHeaders($auth));
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($data['wishes']);
        self::assertSame('accepte', $data['wishes'][0]['status']);
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
