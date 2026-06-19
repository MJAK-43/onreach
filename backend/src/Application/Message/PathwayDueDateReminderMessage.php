<?php

declare(strict_types=1);

namespace App\Application\Message;

final readonly class PathwayDueDateReminderMessage
{
    public function __construct(
        public string $subStepId,
    ) {
    }
}
