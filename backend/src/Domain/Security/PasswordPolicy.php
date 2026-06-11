<?php

declare(strict_types=1);

namespace App\Domain\Security;

final class PasswordPolicy
{
    public const MIN_LENGTH = 12;

    /**
     * @return list<string>
     */
    public static function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = sprintf('Le mot de passe doit contenir au moins %d caractères.', self::MIN_LENGTH);
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
        }

        return $errors;
    }

    public static function isValid(string $password): bool
    {
        return self::validate($password) === [];
    }
}
