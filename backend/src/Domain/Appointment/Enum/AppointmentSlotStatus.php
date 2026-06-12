<?php

declare(strict_types=1);

namespace App\Domain\Appointment\Enum;

enum AppointmentSlotStatus: string
{
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
}
