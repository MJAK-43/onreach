<?php

declare(strict_types=1);

namespace App\Domain\Health;

final readonly class HealthStatus
{
    public function __construct(
        public string $status,
    ) {
    }

    public static function ok(): self
    {
        return new self('ok');
    }

    /**
     * @return array{status: string}
     */
    public function toArray(): array
    {
        return ['status' => $this->status];
    }
}
