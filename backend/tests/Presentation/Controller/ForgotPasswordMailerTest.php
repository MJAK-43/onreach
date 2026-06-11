<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ForgotPasswordMailerTest extends WebTestCase
{
    use DatabaseTestTrait;
    use MailerAssertionsTrait;

    public function testForgotPasswordSendsEmailForExistingUser(): void
    {
        $client = static::createClient();
        $this->resetDatabase();
        $this->seedRbac();

        $client->request(
            'POST',
            '/api/auth/forgot-password',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => 'admin@onreach.inovixora.fr'], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        self::assertEmailCount(1);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('resetToken', $data);
    }
}
