<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegisterApiTest extends WebTestCase
{
    use DatabaseTestTrait;

    private const VALID_PASSWORD = 'Candidate@OnReach12!';

    private function initializeDatabase(): void
    {
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(string $email = 'nouveau.candidat@example.com'): array
    {
        return [
            'firstName' => 'Nouveau',
            'lastName' => 'Candidat',
            'email' => $email,
            'password' => self::VALID_PASSWORD,
            'nationality' => 'Sénégal',
            'studyApplicationType' => 'first_year',
            'phone' => '+221 77 000 00 01',
            'city' => 'Dakar',
            'country' => 'Sénégal',
        ];
    }

    public function testRegisterCreatesCandidateAccountAndReturnsToken(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();

        $client->request(
            'POST',
            '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($this->validPayload(), JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refreshToken', $data);
        $this->assertSame('nouveau.candidat@example.com', $data['user']['email']);
        $this->assertContains('CANDIDATE', $data['user']['roles']);

        $client->request(
            'GET',
            '/api/me/pathways',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$data['token']],
        );

        $this->assertResponseIsSuccessful();
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $codes = array_column($pathways['pathways'], 'code');
        $this->assertContains('campus_france', $codes);
        $this->assertContains('parcoursup', $codes);
        $this->assertSame('first_year', $pathways['studyApplicationType']);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();

        $client->request(
            'POST',
            '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($this->validPayload('admin@onreach.inovixora.fr'), JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterRejectsWeakPassword(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();

        $payload = $this->validPayload();
        $payload['password'] = 'weak';

        $client->request(
            'POST',
            '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterRequiresStudyApplicationType(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();

        $payload = $this->validPayload();
        unset($payload['studyApplicationType']);

        $client->request(
            'POST',
            '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisteredCandidateHasNoAssignedCounselor(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->initializeDatabase();

        $client->request(
            'POST',
            '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($this->validPayload('sans.conseiller@example.com'), JSON_THROW_ON_ERROR),
        );

        $this->assertResponseIsSuccessful();
        $register = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $client->request(
            'GET',
            '/api/candidates',
            server: ['HTTP_AUTHORIZATION' => 'Bearer '.$register['token']],
        );

        $this->assertResponseIsSuccessful();
        $candidates = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $items = $candidates['hydra:member'] ?? $candidates['member'] ?? $candidates;
        $this->assertIsArray($items);
        $this->assertCount(1, $items);
        $this->assertNull($items[0]['assignedCounselor'] ?? null);
    }
}
