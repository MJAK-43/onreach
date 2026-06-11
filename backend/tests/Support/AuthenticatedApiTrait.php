<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticatedApiTrait
{
    /**
     * @return array{token: string, refreshToken: string}
     */
    protected function loginAsAdmin(KernelBrowser $client): array
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'admin@onreach.inovixora.fr',
                'password' => 'Admin@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'token' => $data['token'],
            'refreshToken' => $data['refreshToken'],
        ];
    }

    /**
     * @param array{token: string, refreshToken?: string} $auth
     *
     * @return array<string, string>
     */
    protected function authHeaders(array $auth): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$auth['token'],
            'HTTP_ACCEPT' => 'application/ld+json',
            'CONTENT_TYPE' => 'application/ld+json',
        ];
    }
}
