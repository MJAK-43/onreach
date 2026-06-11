<?php

declare(strict_types=1);

namespace App\Tests\Domain\Security;

use App\Domain\Security\PasswordPolicy;
use PHPUnit\Framework\TestCase;

final class PasswordPolicyTest extends TestCase
{
    public function testValidPasswordPasses(): void
    {
        $this->assertTrue(PasswordPolicy::isValid('ValidPass123!'));
    }

    public function testShortPasswordFails(): void
    {
        $errors = PasswordPolicy::validate('Short1!');
        $this->assertNotEmpty($errors);
    }

    public function testMissingSpecialCharFails(): void
    {
        $errors = PasswordPolicy::validate('ValidPassword123');
        $this->assertContains('Le mot de passe doit contenir au moins un caractère spécial.', $errors);
    }
}
