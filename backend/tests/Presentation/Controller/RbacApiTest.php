<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RbacApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testAdminCanListUsersRolesAndPermissions(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $auth = $this->loginAsAdmin($client);

        foreach (['/api/users', '/api/roles', '/api/permissions'] as $endpoint) {
            $client->request('GET', $endpoint, server: $this->authHeaders($auth));
            $this->assertResponseIsSuccessful("Failed on {$endpoint}");
            $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $members = $data['member'] ?? $data['hydra:member'] ?? [];
            self::assertNotEmpty($members, "Expected collection on {$endpoint}");
        }
    }

    public function testUnauthenticatedUserCannotAccessRbacEndpoints(): void
    {
        $client = static::createClient();
        $this->resetDatabase();
        $this->seedRbac();

        $client->request('GET', '/api/users', server: ['HTTP_ACCEPT' => 'application/ld+json']);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAdminCanCreateUser(): void
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
                'email' => 'newuser@example.com',
                'firstName' => 'New',
                'lastName' => 'User',
                'plainPassword' => 'SecurePass1!',
                'isActive' => true,
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(201);
        $user = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('newuser@example.com', $user['email']);
    }

    public function testPatchRoleCreatesSecurityLog(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $auth = $this->loginAsAdmin($client);

        $client->request('GET', '/api/roles', server: $this->authHeaders($auth));
        $roles = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $adminRole = null;
        foreach ($roles['member'] ?? $roles['hydra:member'] ?? [] as $role) {
            if ('ADMIN' === $role['code']) {
                $adminRole = $role;
                break;
            }
        }
        self::assertNotNull($adminRole);

        $client->request(
            'PATCH',
            '/api/roles/'.$adminRole['id'],
            server: array_merge($this->authHeaders($auth), ['CONTENT_TYPE' => 'application/merge-patch+json']),
            content: json_encode(['name' => 'Administrateur plateforme'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/security_logs', server: $this->authHeaders($auth));
        $this->assertResponseIsSuccessful();
        $logs = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $events = array_column($logs['member'] ?? $logs['hydra:member'] ?? [], 'event');
        self::assertContains('role_updated', $events);
    }

    public function testSystemRoleCannotLoseAllPermissions(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $auth = $this->loginAsAdmin($client);

        $client->request('GET', '/api/roles', server: $this->authHeaders($auth));
        $roles = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $superAdmin = null;
        foreach ($roles['member'] ?? $roles['hydra:member'] ?? [] as $role) {
            if ('SUPER_ADMIN' === $role['code']) {
                $superAdmin = $role;
                break;
            }
        }
        self::assertNotNull($superAdmin);

        $client->request(
            'PATCH',
            '/api/roles/'.$superAdmin['id'],
            server: array_merge($this->authHeaders($auth), ['CONTENT_TYPE' => 'application/merge-patch+json']),
            content: json_encode(['permissions' => []], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(400);
    }
}
