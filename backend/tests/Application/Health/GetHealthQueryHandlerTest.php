<?php

declare(strict_types=1);

namespace App\Tests\Application\Health;

use App\Application\Health\Query\GetHealthQuery;
use App\Application\Health\Query\GetHealthQueryHandler;
use App\Infrastructure\Health\InMemoryHealthRepository;
use PHPUnit\Framework\TestCase;

final class GetHealthQueryHandlerTest extends TestCase
{
    public function testHandlerReturnsOkStatus(): void
    {
        $handler = new GetHealthQueryHandler(new InMemoryHealthRepository());

        $result = $handler(new GetHealthQuery());

        $this->assertSame('ok', $result->status);
    }
}
