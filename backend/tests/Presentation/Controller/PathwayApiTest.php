<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PathwayApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCandidateCanListAssignedPathways(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('first_year', $data['studyApplicationType']);
        self::assertCount(2, $data['pathways']);

        $codes = array_column($data['pathways'], 'code');
        self::assertContains('parcoursup', $codes);
        self::assertContains('campus_france', $codes);

        $campus = $data['pathways'][array_search('campus_france', $codes, true)];
        self::assertNotEmpty($campus['stages']);
        self::assertGreaterThanOrEqual(5, \count($campus['stages'][0]['subSteps']));
    }

    public function testCounselorCanValidateSubStepAndProgressUpdates(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($candidateAuth));
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $pathway = $pathways['pathways'][0];
        $subStepId = $pathway['stages'][0]['subSteps'][0]['id'];
        $pathwayId = $pathway['id'];

        $candidateId = $this->findDemoCandidateId($client, $this->loginAsAdmin($client));

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['validated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $result = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($result['pathway']['stages'][0]['subSteps'][0]['validated']);
        self::assertGreaterThan(0, $result['pathway']['progressPercent']);
    }

    public function testCandidateCannotPatchSubStep(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($auth));
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $pathway = $pathways['pathways'][0];
        $subStepId = $pathway['stages'][0]['subSteps'][0]['id'];
        $pathwayId = $pathway['id'];
        $candidateId = $this->findDemoCandidateId($client, $this->loginAsAdmin($client));

        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($auth),
            content: json_encode(['validated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(403);
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
