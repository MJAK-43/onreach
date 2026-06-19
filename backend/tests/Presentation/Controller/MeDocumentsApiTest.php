<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MeDocumentsApiTest extends WebTestCase
{
    use AuthenticatedApiTrait;
    use DatabaseTestTrait;

    public function testCandidateCanUploadListAndDeleteDocument(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $auth = $this->loginAsCandidate($client);
        $tmp = tempnam(sys_get_temp_dir(), 'cv_');
        file_put_contents($tmp, '%PDF-1.4 test');
        $file = new UploadedFile($tmp, 'cv-test.pdf', 'application/pdf', null, true);

        $client->request(
            'POST',
            '/api/me/documents',
            ['type' => 'cv'],
            ['file' => $file],
            $this->jsonAuthHeaders($auth),
        );
        $this->assertResponseStatusCodeSame(201);
        $uploaded = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('cv', $uploaded['type']);
        self::assertSame('uploaded', $uploaded['status']);
        $documentId = $uploaded['id'];

        $client->request('GET', '/api/me/documents', server: $this->jsonAuthHeaders($auth));
        $this->assertResponseIsSuccessful();
        $list = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($list);

        $client->request(
            'DELETE',
            '/api/me/documents/'.$documentId,
            server: $this->jsonAuthHeaders($auth),
        );
        $this->assertResponseStatusCodeSame(204);
    }

    public function testCounselorCanValidateCandidateDocument(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $candidateAuth = $this->loginAsCandidate($client);
        $tmp = tempnam(sys_get_temp_dir(), 'pass_');
        file_put_contents($tmp, '%PDF-1.4 passport');
        $file = new UploadedFile($tmp, 'passport.pdf', 'application/pdf', null, true);

        $client->request(
            'POST',
            '/api/me/documents',
            ['type' => 'passport'],
            ['file' => $file],
            $this->jsonAuthHeaders($candidateAuth),
        );
        $this->assertResponseStatusCodeSame(201);
        $documentId = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)['id'];

        $candidate = static::getContainer()->get(\App\Repository\CandidateRepository::class)
            ->findOneBy(['email' => 'mohamed.koffi@onreach.inovixora.fr']);
        self::assertNotNull($candidate);

        $counselorAuth = $this->loginAsCounselor($client);
        $client->request(
            'POST',
            '/api/candidates/'.$candidate->getId()->toRfc4122().'/documents/'.$documentId.'/validate',
            server: $this->jsonAuthHeaders($counselorAuth),
        );
        $this->assertResponseIsSuccessful();
        $result = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('validated', $result['status']);
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
