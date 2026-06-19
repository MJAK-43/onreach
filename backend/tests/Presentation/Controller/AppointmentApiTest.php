<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use App\Entity\CounselorAvailabilitySlot;
use App\Entity\User;
use App\Tests\Support\AuthenticatedApiTrait;
use App\Tests\Support\DatabaseTestTrait;
use App\Tests\Support\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AppointmentApiTest extends WebTestCase
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

    public function testCandidateCanBookCounselorSlot(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $counselor = static::getContainer()->get(\App\Repository\UserRepository::class)
            ->findByEmail('marie.kouassi@onreach.inovixora.fr');
        self::assertInstanceOf(User::class, $counselor);

        $startsAt = (new \DateTimeImmutable('+2 days'))->setTime(10, 0);
        $endsAt = $startsAt->modify('+30 minutes');
        $slotRepo = static::getContainer()->get(\App\Repository\CounselorAvailabilitySlotRepository::class);
        $slot = new CounselorAvailabilitySlot($counselor, $startsAt, $endsAt);
        $slotRepo->save($slot);
        self::assertCount(1, $slotRepo->findAll(), 'Le créneau doit être persisté');

        $directSlots = $slotRepo->findAvailableForCounselor(
            $counselor,
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('+60 days'),
        );
        self::assertNotEmpty($directSlots, 'Le créneau doit être visible en base');

        $candidate = static::getContainer()->get(\App\Repository\CandidateRepository::class)
            ->findOneBy(['email' => 'mohamed.koffi@onreach.inovixora.fr']);
        self::assertNotNull($candidate);
        $assignedCounselor = $candidate->getAssignedCounselor();
        self::assertNotNull($assignedCounselor);
        self::assertSame($counselor->getId()->toRfc4122(), $assignedCounselor->getId()->toRfc4122());

        $candidateAuth = $this->login($client, 'mohamed.koffi@onreach.inovixora.fr', 'Candidate@OnReach12!');

        $from = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $to = (new \DateTimeImmutable('+30 days'))->format('Y-m-d');

        $client->request(
            'GET',
            '/api/appointments/available?from='.$from.'&to='.$to,
            server: $this->jsonAuthHeaders($candidateAuth),
        );
        $this->assertResponseIsSuccessful();
        $available = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotNull($available['counselor']);
        self::assertNotEmpty($available['slots']);

        $slotId = $available['slots'][0]['id'];
        $client->request(
            'POST',
            '/api/appointments/book',
            server: $this->jsonAuthHeaders($candidateAuth),
            content: json_encode(['slotId' => $slotId, 'subject' => 'Suivi visa'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/appointments/mine', server: $this->jsonAuthHeaders($candidateAuth));
        $mine = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $mine);
        self::assertSame('booked', $mine[0]['status']);
    }

    public function testCounselorCanCreateAndDeleteAvailabilitySlot(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->resetDatabase();
        $this->seedRbac();
        $this->seedChecklist();
        $this->seedPathways();
        $this->seedDemoViaConsole();

        $counselorAuth = $this->login($client, 'marie.kouassi@onreach.inovixora.fr', 'Counselor@OnReach12!');
        $date = (new \DateTimeImmutable('+3 days'))->format('Y-m-d');

        $client->request(
            'POST',
            '/api/appointments/slots',
            server: $this->jsonAuthHeaders($counselorAuth),
            content: json_encode(['date' => $date, 'time' => '10:00'], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(201);
        $created = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $created);
        self::assertSame('available', $created[0]['status']);
        $slotId = $created[0]['id'];

        $from = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $to = (new \DateTimeImmutable('+30 days'))->format('Y-m-d');
        $client->request(
            'GET',
            '/api/appointments/counselor?from='.$from.'&to='.$to,
            server: $this->jsonAuthHeaders($counselorAuth),
        );
        $this->assertResponseIsSuccessful();
        $schedule = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($schedule);

        $client->request(
            'DELETE',
            '/api/appointments/slots/'.$slotId,
            server: $this->jsonAuthHeaders($counselorAuth),
        );
        $this->assertResponseStatusCodeSame(204);
    }

    /**
     * @return array{token: string, refreshToken: string}
     */
    private function login(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $email, string $password): array
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $password], JSON_THROW_ON_ERROR),
        );

        return json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array{token: string} $auth
     *
     * @return array<string, string>
     */
    private function jsonAuthHeaders(array $auth): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$auth['token'],
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    private function seedDemoViaConsole(): void
    {
        static::getContainer()->get(\App\Infrastructure\Command\SeedDemoUsersCommand::class)->run(
            new \Symfony\Component\Console\Input\ArrayInput([]),
            new \Symfony\Component\Console\Output\NullOutput(),
        );
    }
}
