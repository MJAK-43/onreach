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

    /**
     * @return array{token: string, refreshToken: string}
     */
    protected function loginAsCounselor(KernelBrowser $client): array
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'marie.kouassi@onreach.inovixora.fr',
                'password' => 'Counselor@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'token' => $data['token'],
            'refreshToken' => $data['refreshToken'],
        ];
    }

    /**
     * @return array{token: string, refreshToken: string}
     */
    protected function loginAsCandidate(KernelBrowser $client): array
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'mohamed.koffi@onreach.inovixora.fr',
                'password' => 'Candidate@OnReach12!',
            ], JSON_THROW_ON_ERROR),
        );

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'token' => $data['token'],
            'refreshToken' => $data['refreshToken'],
        ];
    }

    /**
     * @param array{token: string} $auth
     *
     * @return array<string, string>
     */
    protected function jsonAuthHeaders(array $auth): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$auth['token'],
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
    }
}
