<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PathwayTrackingApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;
    use MailerAssertionsTrait;

    public function testStaffCanListPathwayTrackingRows(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/pathways/candidates', server: $this->jsonAuthHeaders($adminAuth));
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertGreaterThan(0, $data['total']);
        self::assertArrayHasKey('pathwayName', $data['items'][0]);
        self::assertArrayHasKey('candidateEmail', $data['items'][0]);
    }

    public function testStaffCanFilterPathwayTrackingByStatus(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request(
            'GET',
            '/api/pathways/candidates?status=in_progress',
            server: $this->jsonAuthHeaders($adminAuth),
        );
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        foreach ($data['items'] as $item) {
            self::assertSame('in_progress', $item['status']);
        }
    }

    public function testStaffCanFetchPathwayStats(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/pathways/stats', server: $this->jsonAuthHeaders($adminAuth));
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertGreaterThan(0, $data['totalPathways']);
        self::assertIsArray($data['byStatus']);
        self::assertIsArray($data['byPathway']);
    }

    public function testCounselorCanBlockPathwayWithReason(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        [$candidateId, $pathwayId] = $this->resolveFirstPathway($client, $this->loginAsAdmin($client));

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s', $candidateId, $pathwayId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode([
                'status' => 'blocked',
                'blockedReason' => 'Document manquant',
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('blocked', $payload['pathway']['status']);
        self::assertSame('Document manquant', $payload['pathway']['blockedReason']);
    }

    public function testAdminCanCreateCampaignAndListTemplates(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request(
            'POST',
            '/api/admin/campaigns',
            server: $this->jsonAuthHeaders($adminAuth),
            content: json_encode([
                'name' => 'Campagne 2027',
                'year' => 2027,
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/admin/pathway-templates', server: $this->jsonAuthHeaders($adminAuth));
        $this->assertResponseIsSuccessful();
        $templates = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $templates['items']);
    }

    public function testCandidateCanOpenConversationWithCounselor(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request(
            'POST',
            '/api/me/conversations/open',
            server: $this->jsonAuthHeaders($candidateAuth),
            content: json_encode(['body' => 'Bonjour, j\'ai une question sur mon dossier.'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/me/conversations', server: $this->jsonAuthHeaders($candidateAuth));
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $data['items']);
    }

    public function testCounselorCanReadCandidateDemarchesOverview(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        $candidateId = $this->findDemoCandidateId($client, $adminAuth);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'GET',
            '/api/candidates/'.$candidateId.'/demarches/overview',
            server: $this->jsonAuthHeaders($counselorAuth),
        );
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('completion', $data);
        self::assertArrayHasKey('procedures', $data);
    }

    public function testCandidateCannotReadOtherCandidateDemarches(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/candidates', server: $this->authHeaders($adminAuth));
        $list = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $members = $list['member'] ?? $list['hydra:member'] ?? [];
        $otherId = null;
        foreach ($members as $member) {
            if (($member['email'] ?? '') === 'ama.diallo@onreach.inovixora.fr') {
                $otherId = $member['id'];
                break;
            }
        }
        self::assertNotNull($otherId);

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request(
            'GET',
            '/api/candidates/'.$otherId.'/demarches/campus-france',
            server: $this->jsonAuthHeaders($candidateAuth),
        );
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @param array{token: string} $adminAuth
     *
     * @return array{0: string, 1: string}
     */
    private function resolveFirstPathway(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, array $adminAuth): array
    {
        $candidateAuth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($candidateAuth));
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return [$this->findDemoCandidateId($client, $adminAuth), $pathways['pathways'][0]['id']];
    }

    /**
     * @param array{token: string} $adminAuth
     */
    private function findDemoCandidateId(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, array $adminAuth): string
    {
        $client->request('GET', '/api/candidates', server: $this->authHeaders($adminAuth));
        $list = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $members = $list['member'] ?? $list['hydra:member'] ?? [];
        foreach ($members as $member) {
            if (($member['email'] ?? '') === 'mohamed.koffi@onreach.inovixora.fr') {
                return $member['id'];
            }
        }

        self::fail('Candidat démo introuvable.');
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
