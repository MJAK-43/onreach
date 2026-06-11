<?php

declare(strict_types=1);

namespace App\Application\Health\Query;

use App\Domain\Health\HealthRepositoryInterface;
use App\Domain\Health\HealthStatus;

final readonly class GetHealthQueryHandler
{
    public function __construct(
        private HealthRepositoryInterface $healthRepository,
    ) {
    }

    public function __invoke(GetHealthQuery $query): HealthStatus
    {
        return $this->healthRepository->getStatus();
    }
}
