<?php

declare(strict_types=1);

namespace App\Infrastructure\Health;

use App\Domain\Health\HealthRepositoryInterface;
use App\Domain\Health\HealthStatus;

final class InMemoryHealthRepository implements HealthRepositoryInterface
{
    public function getStatus(): HealthStatus
    {
        return HealthStatus::ok();
    }
}
