<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testHealthEndpointReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonString(
            '{"status":"ok"}',
            $client->getResponse()->getContent()
        );
    }
}
