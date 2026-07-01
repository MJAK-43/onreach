<?php

declare(strict_types=1);

namespace App\Application\Auth\Command;

use App\Domain\Pathway\Enum\StudyApplicationType;

final readonly class RegisterCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $nationality,
        public StudyApplicationType $studyApplicationType,
        public ?string $phone = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $ip = null,
    ) {
    }
}
