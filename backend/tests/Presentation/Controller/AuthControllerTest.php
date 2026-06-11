<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthControllerTest extends WebTestCase
{
    use DatabaseTestTrait;

    private function initializeDatabase(): void
    {
        $this->resetDatabase();
        $this->seedRbac();
    }

    public function testLoginWithValidCredentialsReturnsToken(): void
    {
        $client = static::createClient();
        $this->initializeDatabase();
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'Admin@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refreshToken', $data);
        $this->assertSame('admin@onreach.inovixora.fr', $data['user']['email']);
    }

    public function testLoginWithInvalidCredentialsReturns401(): void
    {
        $client = static::createClient();
        $this->initializeDatabase();
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'wrong-password',
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeEndpointRequiresAuthentication(): void
    {
        $client = static::createClient();
        $this->initializeDatabase();
        $client->request('GET', '/api/me');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeEndpointReturnsCurrentUser(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'Admin@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );
        $login = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $client->request(
            'GET',
            '/api/me',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$login['token']],
        );

        $this->assertResponseIsSuccessful();
        $me = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('admin@onreach.inovixora.fr', $me['email']);
        $this->assertContains('users.view', $me['permissions']);
    }

    public function testForgotPasswordDoesNotRevealUserExistence(): void
    {
        $client = static::createClient();
        $this->initializeDatabase();
        $client->request(
            'POST',
            '/api/auth/forgot-password',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'unknown@example.com'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('message', $data);
    }
}
