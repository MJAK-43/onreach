<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MeProfileApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCandidateCanReadAndUpdateProfile(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);

        $client->request('GET', '/api/me/profile', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();
        $profile = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('completion', $profile);
        self::assertArrayHasKey('personal', $profile);

        $client->request(
            'PUT',
            '/api/me/profile',
            server: $this->jsonAuthHeaders($auth),
            content: json_encode([
                'contact' => ['city' => 'Abidjan', 'country' => 'Côte d\'Ivoire'],
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();
        $updated = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Abidjan', $updated['contact']['city']);

        $client->request('GET', '/api/me/completion', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/me/history', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCannotAccessMeProfile(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();

        $auth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/me/profile', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseStatusCodeSame(403);
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
