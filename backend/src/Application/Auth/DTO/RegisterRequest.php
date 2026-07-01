<?php

declare(strict_types=1);

namespace App\Application\Auth\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $firstName = '',
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $lastName = '',
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',
        #[Assert\NotBlank]
        public string $password = '',
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $nationality = '',
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['first_year', 'continuing'])]
        public string $studyApplicationType = '',
        public ?string $phone = null,
        public ?string $city = null,
        public ?string $country = null,
    ) {
    }
}
