<?php

declare(strict_types=1);

namespace App\Domain\Health;

interface HealthRepositoryInterface
{
    public function getStatus(): HealthStatus;
}
