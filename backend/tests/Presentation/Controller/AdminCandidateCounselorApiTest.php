<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminCandidateCounselorApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testAdminCanAssignCounselorViaPatchEndpoint(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoUsers();

        $adminAuth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/candidates',
            server: $this->authHeaders($adminAuth),
            content: json_encode([
                'firstName' => 'Awa',
                'lastName' => 'Diop',
                'email' => 'awa.diop@example.com',
                'nationality' => 'Sénégal',
                'status' => 'lead',
                'studyApplicationType' => 'first_year',
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);
        $candidate = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $client->request('GET', '/api/users', server: $this->authHeaders($adminAuth));
        $this->assertResponseIsSuccessful();
        $users = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $members = $users['member'] ?? $users['hydra:member'] ?? [];
        $counselorId = null;
        foreach ($members as $user) {
            if (str_contains((string) ($user['email'] ?? ''), 'marie.kouassi')) {
                $counselorId = $user['id'];
                break;
            }
        }
        self::assertNotNull($counselorId);

        $client->request(
            'PATCH',
            '/api/admin/candidates/'.$candidate['id'].'/counselor',
            server: $this->jsonAuthHeaders($adminAuth),
            content: json_encode(['counselorId' => $counselorId], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();
        $assigned = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($counselorId, $assigned['assignedCounselor']['id'] ?? null);
    }

    private function seedDemoUsers(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
