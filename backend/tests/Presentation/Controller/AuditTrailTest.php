<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuditTrailTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCreatingUserWritesAuditTrail(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $auth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/users',
            server: $this->authHeaders($auth),
            content: json_encode([
                'email' => 'audited@example.com',
                'firstName' => 'Audited',
                'lastName' => 'User',
                'plainPassword' => 'SecurePass1!',
                'isActive' => true,
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/audit_trails', server: $this->authHeaders($auth));
        $this->assertResponseIsSuccessful();
        $trails = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $items = $trails['member'] ?? $trails['hydra:member'] ?? [];

        $created = array_filter($items, static fn (array $item): bool => 'created' === $item['action'] && 'User' === $item['entityType']);
        self::assertNotEmpty($created);
    }
}
