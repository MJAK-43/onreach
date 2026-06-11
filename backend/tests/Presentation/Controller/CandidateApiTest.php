<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CandidateApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testAdminCanCreateAndListCandidates(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $auth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/candidates',
            server: $this->authHeaders($auth),
            content: json_encode([
                'firstName' => 'Aissatou',
                'lastName' => 'Diallo',
                'email' => 'aissatou@example.com',
                'nationality' => 'Sénégal',
                'status' => 'lead',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertStringStartsWith('ONR-', $created['referenceNumber'] ?? '');
        self::assertSame('aissatou@example.com', $created['email']);

        $client->request('GET', '/api/candidates', server: $this->authHeaders($auth));
        $this->assertResponseIsSuccessful();
        $list = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $members = $list['member'] ?? $list['hydra:member'] ?? [];
        self::assertCount(1, $members);
    }

    public function testCandidateTimelineAfterCreation(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $auth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/candidates',
            server: $this->authHeaders($auth),
            content: json_encode([
                'firstName' => 'Mohamed',
                'lastName' => 'Koffi',
                'email' => 'mohamed@example.com',
                'nationality' => 'Côte d\'Ivoire',
                'status' => 'in_progress',
            ], JSON_THROW_ON_ERROR),
        );
        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $id = $created['id'];

        $client->request(
            'GET',
            "/api/candidates/{$id}/timeline",
            server: array_merge($this->authHeaders($auth), ['HTTP_ACCEPT' => 'application/json']),
        );
        $this->assertResponseIsSuccessful();
        $timeline = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($timeline);
        self::assertSame('candidate.created', $timeline[0]['action']);
    }

    public function testCompletionEndpointReturnsChecklist(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $auth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/candidates',
            server: $this->authHeaders($auth),
            content: json_encode([
                'firstName' => 'Fatou',
                'lastName' => 'Sow',
                'email' => 'fatou@example.com',
                'nationality' => 'Sénégal',
                'status' => 'lead',
            ], JSON_THROW_ON_ERROR),
        );
        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $client->request(
            'GET',
            '/api/candidates/'.$created['id'].'/completion',
            server: array_merge($this->authHeaders($auth), ['HTTP_ACCEPT' => 'application/json']),
        );
        $this->assertResponseIsSuccessful();
        $completion = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('campusFrance', $completion);
        self::assertArrayHasKey('checklist', $completion);
        self::assertNotEmpty($completion['checklist']['items']);
    }

    public function testUnauthenticatedCannotAccessCandidates(): void
    {
        $client = static::createClient();
        $this->resetDatabase();
        $this->seedRbac();

        $client->request('GET', '/api/candidates', server: ['HTTP_ACCEPT' => 'application/ld+json']);
        $this->assertResponseStatusCodeSame(401);
    }
}
