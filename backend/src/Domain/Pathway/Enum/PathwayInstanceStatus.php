<?php

declare(strict_types=1);

namespace App\Domain\Pathway\Enum;

enum PathwayInstanceStatus: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case REFUSED = 'refused';
    case ACCEPTED = 'accepted';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::NOT_STARTED => 'Non démarré',
            self::IN_PROGRESS => 'En cours',
            self::BLOCKED => 'Bloqué',
            self::REFUSED => 'Refusé',
            self::ACCEPTED => 'Accepté',
            self::ABANDONED => 'Abandonné',
        };
    }
}
