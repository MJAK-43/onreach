<?php

declare(strict_types=1);

namespace App\Tests\Domain\Health;

use App\Domain\Health\HealthStatus;
use PHPUnit\Framework\TestCase;

final class HealthStatusTest extends TestCase
{
    public function testOkFactoryReturnsOkStatus(): void
    {
        $status = HealthStatus::ok();

        $this->assertSame('ok', $status->status);
        $this->assertSame(['status' => 'ok'], $status->toArray());
    }
}
