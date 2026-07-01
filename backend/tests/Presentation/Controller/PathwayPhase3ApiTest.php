<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PathwayPhase3ApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;
    use MailerAssertionsTrait;

    public function testDoubleValidationRequiresAdminBeforeStepIsValidated(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();
        $this->resetParcoursupValidationsForDemoCandidate();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request(
            'PATCH',
            '/api/admin/pathway-settings/parcoursup',
            server: $this->jsonAuthHeaders($adminAuth),
            content: json_encode(['doubleValidationEnabled' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        [$candidateId, $pathwayId, $subStepId] = $this->resolveFirstSubStep($client, $adminAuth);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['counselorValidated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $afterCounselor = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotNull($afterCounselor['pathway']['stages'][0]['subSteps'][0]['counselorValidatedAt']);
        self::assertFalse($afterCounselor['pathway']['stages'][0]['subSteps'][0]['validated']);

        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($adminAuth),
            content: json_encode(['adminValidated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $afterAdmin = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($afterAdmin['pathway']['stages'][0]['subSteps'][0]['validated']);
        self::assertGreaterThan(0, $afterAdmin['pathway']['progressPercent']);
    }

    public function testCounselorValidationsAreGrandfatheredWhenDoubleValidationIsEnabledLater(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();
        $this->resetParcoursupValidationsForDemoCandidate();

        $adminAuth = $this->loginAsAdmin($client);
        [$candidateId, $pathwayId, $firstSubStepId] = $this->resolveFirstSubStep($client, $adminAuth);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $firstSubStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['counselorValidated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $afterCounselor = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($afterCounselor['pathway']['stages'][0]['subSteps'][0]['validated']);
        self::assertTrue($afterCounselor['pathway']['stages'][0]['subSteps'][0]['grandfatheredValidation']);
        self::assertGreaterThan(0, $afterCounselor['pathway']['progressPercent']);

        $client->request(
            'PATCH',
            '/api/admin/pathway-settings/parcoursup',
            server: $this->jsonAuthHeaders($adminAuth),
            content: json_encode(['doubleValidationEnabled' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($candidateAuth));
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $pathway = null;
        foreach ($pathways['pathways'] as $item) {
            if ('parcoursup' === $item['code']) {
                $pathway = $item;
                break;
            }
        }
        self::assertNotNull($pathway);
        self::assertTrue($pathway['stages'][0]['subSteps'][0]['validated']);
        self::assertTrue($pathway['stages'][0]['subSteps'][0]['grandfatheredValidation']);
        self::assertGreaterThan(0, $pathway['progressPercent']);

        $secondSubStepId = $pathway['stages'][0]['subSteps'][1]['id'];
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $secondSubStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['counselorValidated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $afterSecondCounselor = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertFalse($afterSecondCounselor['pathway']['stages'][0]['subSteps'][1]['validated']);
        self::assertFalse($afterSecondCounselor['pathway']['stages'][0]['subSteps'][1]['grandfatheredValidation']);
    }

    public function testPathwayAuditIsReadableByStaff(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $adminAuth = $this->loginAsAdmin($client);
        [$candidateId, $pathwayId, $subStepId] = $this->resolveFirstSubStep($client, $adminAuth);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['validated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();

        $em = $client->getContainer()->get('doctrine')->getManager();
        $em->clear();
        $candidate = $em->getRepository(\App\Entity\Candidate::class)->find($candidateId);
        self::assertNotNull($candidate);
        $logs = $em->getRepository(\App\Entity\PathwayAuditLog::class)->findBy(['candidate' => $candidate], ['occurredAt' => 'DESC'], 10);
        self::assertNotEmpty($logs);
        $actions = array_map(static fn ($log) => $log->getAction(), $logs);
        self::assertContains('substep.validation_updated', $actions);
    }

    public function testValidationCreatesCandidateNotificationAndEmail(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();
        $this->seedDemoViaConsole();
        $this->resetParcoursupValidationsForDemoCandidate();

        $adminAuth = $this->loginAsAdmin($client);
        [$candidateId, $pathwayId, $subStepId] = $this->resolveFirstSubStep($client, $adminAuth);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'PATCH',
            sprintf('/api/candidates/%s/pathways/%s/sub-steps/%s', $candidateId, $pathwayId, $subStepId),
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['validated' => true], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseIsSuccessful();
        self::assertEmailCount(1);

        $em = $client->getContainer()->get('doctrine')->getManager();
        $em->clear();
        $totalNotifications = (int) $em->createQuery('SELECT COUNT(n.id) FROM App\Entity\InAppNotification n')->getSingleScalarResult();
        self::assertSame(1, $totalNotifications);

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/notifications', server: $this->jsonAuthHeaders($candidateAuth));
        $this->assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(1, $payload['unreadCount']);
        self::assertSame('pathway.substep_validated', $payload['items'][0]['type']);
    }

    public function testAdminCanListPathwaySettings(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedPathways();

        $adminAuth = $this->loginAsAdmin($client);
        $client->request('GET', '/api/admin/pathway-settings', server: $this->jsonAuthHeaders($adminAuth));
        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $data['items']);
    }

    /**
     * @param array{token: string} $adminAuth
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolveFirstSubStep(
        \Symfony\Bundle\FrameworkBundle\KernelBrowser $client,
        array $adminAuth,
        string $pathwayCode = 'parcoursup',
    ): array {
        $candidateId = $this->findDemoCandidateId($client, $adminAuth);

        $candidateAuth = $this->loginAsCandidate($client);
        $client->request('GET', '/api/me/pathways', server: $this->jsonAuthHeaders($candidateAuth));
        $pathways = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $pathway = null;
        foreach ($pathways['pathways'] as $item) {
            if ($item['code'] === $pathwayCode) {
                $pathway = $item;
                break;
            }
        }
        self::assertNotNull($pathway, sprintf('Parcours %s introuvable.', $pathwayCode));

        return [
            $candidateId,
            $pathway['id'],
            $pathway['stages'][0]['subSteps'][0]['id'],
        ];
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

    private function resetParcoursupValidationsForDemoCandidate(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $candidate = $em->getRepository(\App\Entity\Candidate::class)->findOneBy([
            'email' => 'mohamed.koffi@onreach.inovixora.fr',
        ]);
        self::assertNotNull($candidate);

        foreach ($candidate->getPathways() as $pathway) {
            if ('parcoursup' !== $pathway->getPathwayTemplate()->getCode()->value) {
                continue;
            }

            foreach ($pathway->getStages() as $stage) {
                foreach ($stage->getSubSteps() as $subStep) {
                    $subStep->clearValidation();
                }
            }

            static::getContainer()->get(\App\Infrastructure\Pathway\PathwayProgressCalculator::class)->refresh($pathway);
        }

        $em->flush();
        $em->clear();
    }
}
