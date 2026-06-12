<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Entity\AuditTrail;
use App\Repository\AuditTrailRepository;
use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use App\Tests\Support\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuditTrailTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;
    use WebTestCaseTrait;

    protected function setUp(): void
    {
        $this->setUpWebTestCase();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->tearDownWebTestCase();

        parent::tearDown();
    }

    public function testCreatingUserWritesAuditTrail(): void
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
                'email' => 'audited@example.com',
                'firstName' => 'Audited',
                'lastName' => 'User',
                'plainPassword' => 'SecurePass1!',
                'isActive' => true,
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);

        /** @var AuditTrailRepository $auditRepository */
        $auditRepository = static::getContainer()->get(AuditTrailRepository::class);
        $created = array_filter(
            $auditRepository->findAll(),
            static fn (AuditTrail $audit): bool => 'created' === $audit->getAction()
                && 'User' === $audit->getEntityType()
                && 'audited@example.com' === ($audit->getNewValue()['email'] ?? null),
        );
        self::assertNotEmpty($created);
    }
}
