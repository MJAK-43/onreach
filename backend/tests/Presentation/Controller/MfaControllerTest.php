<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MfaControllerTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testMfaSetupEnableAndLoginFlow(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();

        $auth = $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/auth/mfa/setup',
            server: $this->authHeaders($auth),
        );
        $this->assertResponseIsSuccessful();
        $setup = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('secret', $setup);
        self::assertArrayHasKey('qrCode', $setup);

        $code = TOTP::create($setup['secret'])->now();
        $client->request(
            'POST',
            '/api/auth/mfa/enable',
            server: $this->authHeaders($auth),
            content: json_encode(['code' => $code], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();
        $enable = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(8, $enable['recoveryCodes']);

        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'Admin@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );
        $loginMfaRequired = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($loginMfaRequired['requiresMfa']);

        $mfaCode = TOTP::create($setup['secret'])->now();
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'Admin@OnReach12!',
                'mfaCode' => $mfaCode,
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();
        $login = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('token', $login);
    }
}
